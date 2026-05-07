<?php

namespace Tests\Feature;

use App\Mail\SingleEmailMail;
use App\Models\Account;
use App\Models\EmailQueue;
use App\Models\SmtpServer;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SingleEmailFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createAccount(): Account
    {
        return Account::query()->create([
            'name' => 'Account ' . Str::random(8),
            'slug' => Str::slug('account-' . Str::random(8)),
            'status' => 'active',
        ]);
    }

    private function actingUserForAccount(Account $account): User
    {
        return User::factory()->create([
            'account_id' => $account->id,
            'role' => 'admin',
        ]);
    }

    private function createSmtp(Account $account): SmtpServer
    {
        return SmtpServer::create([
            'account_id' => $account->id,
            'name' => 'Primary SMTP',
            'host' => 'smtp.example.test',
            'port' => 587,
            'username' => 'user@example.test',
            'password' => 'secret-pass',
            'encryption' => 'tls',
            'from_email' => 'from@example.test',
            'from_name' => 'Mailer',
            'is_active' => true,
            'daily_limit' => 100,
            'priority' => 1,
        ]);
    }

    public function test_single_email_send_success_persists_queue_row_and_dispatches_mail(): void
    {
        Storage::fake('local');
        Mail::fake();

        $account = $this->createAccount();
        $user = $this->actingUserForAccount($account);
        $smtp = $this->createSmtp($account);

        $response = $this->actingAs($user)->post(route('single-email.store'), [
            'to' => 'receiver@example.test',
            'subject' => 'Hello',
            'message' => '<p>Hi <a href="https://example.com">click</a></p>',
            'smtp_server_id' => $smtp->id,
        ]);

        $response->assertRedirect(route('single-email.create'));
        $response->assertSessionHas('success');

        $queue = EmailQueue::query()->latest('id')->first();
        $this->assertNotNull($queue);
        $this->assertSame('single', $queue->type);
        $this->assertSame('receiver@example.test', $queue->email);
        $this->assertSame('sent', $queue->status);
        $this->assertSame($smtp->id, $queue->smtp_server_id);

        Mail::assertSent(SingleEmailMail::class);
    }

    public function test_invalid_email_validation_fails(): void
    {
        $account = $this->createAccount();
        $user = $this->actingUserForAccount($account);
        $smtp = $this->createSmtp($account);

        $response = $this->from(route('single-email.create'))->actingAs($user)->post(route('single-email.store'), [
            'to' => 'not-an-email',
            'subject' => 'Hello',
            'message' => '<p>Body</p>',
            'smtp_server_id' => $smtp->id,
        ]);

        $response->assertRedirect(route('single-email.create'));
        $response->assertSessionHasErrors(['to']);
    }

    public function test_single_email_send_with_cc_and_bcc_dispatches_mail(): void
    {
        Storage::fake('local');
        Mail::fake();

        $account = $this->createAccount();
        $user = $this->actingUserForAccount($account);
        $smtp = $this->createSmtp($account);

        $response = $this->actingAs($user)->post(route('single-email.store'), [
            'to' => 'receiver@example.test',
            'cc' => 'copy1@example.test, copy2@example.test',
            'bcc' => 'hidden1@example.test, hidden2@example.test',
            'subject' => 'Hello with CC BCC',
            'message' => '<p>Hi</p>',
            'smtp_server_id' => $smtp->id,
        ]);

        $response->assertRedirect(route('single-email.create'));
        $response->assertSessionHas('success');

        Mail::assertSent(SingleEmailMail::class, function (SingleEmailMail $mail): bool {
            $cc = collect($mail->cc ?? [])->pluck('address')->values()->all();
            $bcc = collect($mail->bcc ?? [])->pluck('address')->values()->all();

            return in_array('copy1@example.test', $cc, true)
                && in_array('copy2@example.test', $cc, true)
                && in_array('hidden1@example.test', $bcc, true)
                && in_array('hidden2@example.test', $bcc, true);
        });
    }

    public function test_invalid_cc_or_bcc_validation_fails(): void
    {
        $account = $this->createAccount();
        $user = $this->actingUserForAccount($account);
        $smtp = $this->createSmtp($account);

        $response = $this->from(route('single-email.create'))->actingAs($user)->post(route('single-email.store'), [
            'to' => 'receiver@example.test',
            'cc' => 'valid@example.test, invalid-email',
            'bcc' => 'hidden@example.test',
            'subject' => 'Hello',
            'message' => '<p>Body</p>',
            'smtp_server_id' => $smtp->id,
        ]);

        $response->assertRedirect(route('single-email.create'));
        $response->assertSessionHasErrors(['cc']);
    }

    public function test_smtp_selection_is_required(): void
    {
        $account = $this->createAccount();
        $user = $this->actingUserForAccount($account);

        $response = $this->from(route('single-email.create'))->actingAs($user)->post(route('single-email.store'), [
            'to' => 'receiver@example.test',
            'subject' => 'Hello',
            'message' => '<p>Body</p>',
        ]);

        $response->assertRedirect(route('single-email.create'));
        $response->assertSessionHasErrors(['smtp_server_id']);
    }

    public function test_cross_account_smtp_is_blocked(): void
    {
        $accountA = $this->createAccount();
        $accountB = $this->createAccount();

        $userA = $this->actingUserForAccount($accountA);
        $smtpB = $this->createSmtp($accountB);

        $response = $this->actingAs($userA)->post(route('single-email.store'), [
            'to' => 'receiver@example.test',
            'subject' => 'Hello',
            'message' => '<p>Body</p>',
            'smtp_server_id' => $smtpB->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_attachments_are_accepted_and_queue_row_stores_metadata(): void
    {
        Storage::fake('local');
        Mail::fake();

        $account = $this->createAccount();
        $user = $this->actingUserForAccount($account);
        $smtp = $this->createSmtp($account);

        $file = UploadedFile::fake()->create('sample.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->post(route('single-email.store'), [
            'to' => 'receiver@example.test',
            'subject' => 'With attachment',
            'message' => '<p>Body</p>',
            'smtp_server_id' => $smtp->id,
            'attachments' => [$file],
        ]);

        $response->assertRedirect(route('single-email.create'));

        $queue = EmailQueue::query()->latest('id')->first();
        $this->assertIsArray($queue->attachments);
        $this->assertNotEmpty($queue->attachments);
        $this->assertArrayHasKey('name', $queue->attachments[0]);
        $this->assertSame('sample.pdf', $queue->attachments[0]['name']);
    }

    public function test_tracking_links_and_open_pixel_are_present_in_generated_email(): void
    {
        Storage::fake('local');

        $account = $this->createAccount();
        $user = $this->actingUserForAccount($account);
        $smtp = $this->createSmtp($account);

        $this->actingAs($user)->post(route('single-email.store'), [
            'to' => 'receiver@example.test',
            'subject' => 'Tracking Test',
            'message' => '<p><a href="https://example.com/path">Click me</a></p>',
            'smtp_server_id' => $smtp->id,
        ]);

        $queue = EmailQueue::query()->latest('id')->first();
        $mail = new SingleEmailMail($queue, 'Tracking Test', '<p><a href="https://example.com/path">Click me</a></p>', []);
        $html = $mail->render();

        $this->assertMatchesRegularExpression('/<a\s+[^>]*href=("|\')https?:\/\/[^"\']+\1[^>]*>/i', $html);
        $this->assertStringContainsString('/track/click/', $html);
        $this->assertStringContainsString(route('track.open', ['id' => $queue->id]), $html);
        $this->assertStringNotContainsString('&amp;', $html);
        $this->assertMatchesRegularExpression('/href="[^"]+"/', $html);
        $this->assertStringNotContainsString('/track/click?url=/unsubscribe', $html);
    }

    public function test_mailto_tel_and_unsubscribe_links_are_not_rewritten(): void
    {
        Storage::fake('local');

        $account = $this->createAccount();
        $user = $this->actingUserForAccount($account);
        $smtp = $this->createSmtp($account);

        $message = '<p>'
            . '<a href="mailto:test@example.com">Mail</a> '
            . '<a href="tel:+1234567890">Call</a> '
            . '<a href="https://example.com/unsubscribe/me">Unsub</a> '
            . '<a href="https://example.com/page">Page</a>'
            . '</p>';

        $this->actingAs($user)->post(route('single-email.store'), [
            'to' => 'receiver@example.test',
            'subject' => 'Protocol Test',
            'message' => $message,
            'smtp_server_id' => $smtp->id,
        ]);

        $queue = EmailQueue::query()->latest('id')->first();
        $mail = new SingleEmailMail($queue, 'Protocol Test', $message, []);
        $html = $mail->render();

        $this->assertStringContainsString('href="mailto:test@example.com"', $html);
        $this->assertStringContainsString('href="tel:+1234567890"', $html);
        $this->assertStringContainsString('href="https://example.com/unsubscribe/me"', $html);
        $this->assertStringContainsString(route('track.click', ['id' => $queue->id]), $html);
    }
}
