<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Plan;
use App\Models\SmtpServer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SMTPManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createAccountWithUser(string $email = 'owner@example.com'): array
    {
        $plan = Plan::create([
            'name' => 'Free',
            'slug' => 'free-plan-'.uniqid(),
            'emails_per_day' => 100,
            'campaigns_limit' => 3,
        ]);

        $user = User::factory()->create([
            'email' => $email,
            'role' => 'admin',
        ]);

        $account = Account::create([
            'name' => 'Acme',
            'plan_id' => $plan->id,
            'owner_user_id' => $user->id,
        ]);

        $user->account_id = $account->id;
        $user->save();

        DB::table('account_user')->insert([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$account, $user];
    }

    public function test_account_scoped_create_update_delete_smtp_works(): void
    {
        [$account, $user] = $this->createAccountWithUser();

        $this->actingAs($user)
            ->post(route('smtp.store'), [
                'name' => 'Primary SMTP',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'smtp-user',
                'password' => 'secret12345',
                'encryption' => 'tls',
                'from_email' => 'no-reply@example.com',
                'from_name' => 'Acme',
                'daily_limit' => 500,
                'priority' => 1,
            ])
            ->assertRedirect(route('smtp.index'));

        $smtp = SmtpServer::where('account_id', $account->id)->where('name', 'Primary SMTP')->first();
        $this->assertNotNull($smtp);

        $this->actingAs($user)
            ->put(route('smtp.update', $smtp), [
                'name' => 'Primary SMTP Updated',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'smtp-user',
                'password' => '',
                'encryption' => 'tls',
                'from_email' => 'no-reply@example.com',
                'from_name' => 'Acme Updated',
                'daily_limit' => 600,
                'priority' => 2,
            ])
            ->assertRedirect(route('smtp.index'));

        $smtp->refresh();
        $this->assertSame('Primary SMTP Updated', $smtp->name);
        $this->assertSame('Acme Updated', $smtp->from_name);

        $this->actingAs($user)
            ->delete(route('smtp.destroy', $smtp))
            ->assertRedirect(route('smtp.index'));

        $this->assertDatabaseMissing('smtp_servers', ['id' => $smtp->id]);
    }

    public function test_cross_account_access_is_denied_with_403(): void
    {
        [$accountA, $userA] = $this->createAccountWithUser('a@example.com');
        [$accountB, $userB] = $this->createAccountWithUser('b@example.com');

        $smtpB = SmtpServer::create([
            'account_id' => $accountB->id,
            'name' => 'B SMTP',
            'host' => 'smtp.office365.com',
            'port' => 587,
            'username' => 'b-user',
            'password' => 'password123',
            'encryption' => 'tls',
            'from_email' => 'b@example.com',
            'from_name' => 'B',
            'is_active' => true,
        ]);

        $this->actingAs($userA)
            ->get(route('smtp.edit', $smtpB))
            ->assertForbidden();

        $this->actingAs($userA)
            ->put(route('smtp.update', $smtpB), [
                'name' => 'Hack',
                'host' => 'smtp.office365.com',
                'port' => 587,
                'username' => 'hack',
                'password' => '',
                'encryption' => 'tls',
                'from_email' => 'hack@example.com',
                'from_name' => 'Hack',
            ])
            ->assertForbidden();

        $this->actingAs($userA)
            ->patch(route('smtp.toggle', $smtpB))
            ->assertForbidden();

        $this->actingAs($userA)
            ->delete(route('smtp.destroy', $smtpB))
            ->assertForbidden();

        $this->assertDatabaseHas('smtp_servers', ['id' => $smtpB->id]);
    }

    public function test_bulk_upload_inserts_valid_rows_and_skips_invalid_rows(): void
    {
        [$account, $user] = $this->createAccountWithUser();

        SmtpServer::create([
            'account_id' => $account->id,
            'name' => 'Existing',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => 'existing-user',
            'password' => 'password123',
            'encryption' => 'tls',
            'from_email' => 'existing@example.com',
            'from_name' => 'Existing',
            'is_active' => true,
        ]);

        $csv = implode("\n", [
            'label,host,port,username,password,encryption,from_email,from_name',
            'Valid One,smtp.zoho.com,587,zoho-user,pass12345,tls,valid1@example.com,Valid One',
            'Invalid Email,smtp.gmail.com,587,user2,pass12345,tls,not-an-email,Invalid Email',
            'Duplicate HostUser,smtp.gmail.com,587,existing-user,pass12345,tls,dup@example.com,Dup Host',
        ]);

        $file = UploadedFile::fake()->createWithContent('smtp.csv', $csv);

        $response = $this->actingAs($user)
            ->post(route('smtp.bulk-upload'), [
                'smtp_csv' => $file,
            ]);

        $response->assertRedirect(route('smtp.index'));
        $response->assertSessionHas('smtp_bulk_success_count', 1);

        $failedRows = session('smtp_bulk_failed_rows', []);
        $this->assertCount(2, $failedRows);

        $this->assertDatabaseHas('smtp_servers', [
            'account_id' => $account->id,
            'name' => 'Valid One',
            'host' => 'smtp.zoho.com',
            'username' => 'zoho-user',
        ]);
    }

    public function test_password_is_stored_encrypted_in_database(): void
    {
        [$account, $user] = $this->createAccountWithUser();

        $plain = 'super-secret-password';

        $this->actingAs($user)
            ->post(route('smtp.store'), [
                'name' => 'Encrypted SMTP',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'enc-user',
                'password' => $plain,
                'encryption' => 'tls',
                'from_email' => 'enc@example.com',
                'from_name' => 'Enc',
            ])
            ->assertRedirect(route('smtp.index'));

        $smtp = SmtpServer::where('account_id', $account->id)->where('name', 'Encrypted SMTP')->firstOrFail();

        $rawPassword = DB::table('smtp_servers')->where('id', $smtp->id)->value('password');

        $this->assertNotSame($plain, $rawPassword);
        $this->assertSame($plain, $smtp->password);
    }
}
