<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContactManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(?int $accountId = null): User
    {
        if ($accountId === null) {
            $accountId = $this->createAccountId();
        }

        $user = User::factory()->create([
            'account_id' => $accountId,
            'role' => 'manager',
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_create_contact_valid_and_assign_group(): void
    {
        $this->actingAsUser();
        $group = Group::create(['name' => 'Leads']);

        $response = $this->post(route('contacts.store'), [
            'name' => 'Alice Smith',
            'business_name' => 'Alice Ventures',
            'email' => 'alice@example.com',
            'website' => 'https://alice.example.com',
            'groups' => [$group->id],
        ]);

        $response->assertRedirect(route('contacts.index'));
        $this->assertDatabaseHas('contacts', [
            'name' => 'Alice Smith',
            'business_name' => 'Alice Ventures',
            'email' => 'alice@example.com',
            'website' => 'https://alice.example.com',
        ]);

        $contact = Contact::where('email', 'alice@example.com')->firstOrFail();
        $this->assertDatabaseHas('contact_group', [
            'contact_id' => $contact->id,
            'group_id' => $group->id,
        ]);
    }

    public function test_create_contact_invalid_validation(): void
    {
        $this->actingAsUser();

        $response = $this->post(route('contacts.store'), [
            'name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_prevent_duplicate_emails(): void
    {
        $this->actingAsUser();

        Contact::create([
            'name' => 'Existing',
            'email' => 'dup@example.com',
        ]);

        $response = $this->post(route('contacts.store'), [
            'name' => 'Duplicate',
            'email' => 'dup@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('contacts', 1);
    }

    public function test_update_contact_and_groups(): void
    {
        $this->actingAsUser();

        $contact = Contact::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
        ]);

        $groupA = Group::create(['name' => 'Leads']);
        $groupB = Group::create(['name' => 'Clients']);

        $contact->groups()->sync([$groupA->id]);

        $response = $this->put(route('contacts.update', $contact), [
            'name' => 'Bobby',
            'business_name' => 'Bobby Co',
            'email' => 'bobby@example.com',
            'website' => 'https://bobby.example.com',
            'groups' => [$groupB->id],
        ]);

        $response->assertRedirect(route('contacts.index'));

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'name' => 'Bobby',
            'business_name' => 'Bobby Co',
            'email' => 'bobby@example.com',
            'website' => 'https://bobby.example.com',
        ]);

        $this->assertDatabaseMissing('contact_group', [
            'contact_id' => $contact->id,
            'group_id' => $groupA->id,
        ]);

        $this->assertDatabaseHas('contact_group', [
            'contact_id' => $contact->id,
            'group_id' => $groupB->id,
        ]);
    }

    public function test_delete_contact(): void
    {
        $this->actingAsUser();

        $contact = Contact::create([
            'name' => 'Delete Me',
            'email' => 'delete@example.com',
        ]);

        $response = $this->delete(route('contacts.destroy', $contact));

        $response->assertRedirect(route('contacts.index'));
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    public function test_csv_import_mixed_rows_duplicates_and_summary_with_group_assignment(): void
    {
        $this->actingAsUser();

        $group = Group::create(['name' => 'Import Group']);

        Contact::create([
            'name' => 'Existing DB',
            'email' => 'existing@example.com',
        ]);

        $csv = implode("\n", [
            'name,email',
            'Valid One,valid1@example.com',
            'Invalid Email,not-an-email',
            'Existing DB,existing@example.com',
            'Valid One Duplicate In File,valid1@example.com',
            ',missingname@example.com',
            'Valid Two,valid2@example.com',
        ]);

        $file = UploadedFile::fake()->createWithContent('contacts.csv', $csv);

        $response = $this->post(route('import.store'), [
            'csv_file' => $file,
            'name_column' => 'name',
            'email_column' => 'email',
            'groups' => [$group->id],
        ]);

        $response->assertOk();
        $response->assertViewIs('import.result');
        $response->assertViewHas('total', 6);
        $response->assertViewHas('imported', 2);
        $response->assertViewHas('skipped', 4);

        $this->assertDatabaseHas('contacts', [
            'name' => 'Valid One',
            'email' => 'valid1@example.com',
        ]);

        $this->assertDatabaseHas('contacts', [
            'name' => 'Valid Two',
            'email' => 'valid2@example.com',
        ]);

        $validOne = Contact::where('email', 'valid1@example.com')->firstOrFail();
        $validTwo = Contact::where('email', 'valid2@example.com')->firstOrFail();

        $this->assertDatabaseHas('contact_group', [
            'contact_id' => $validOne->id,
            'group_id' => $group->id,
        ]);

        $this->assertDatabaseHas('contact_group', [
            'contact_id' => $validTwo->id,
            'group_id' => $group->id,
        ]);
    }

    public function test_bulk_delete_success_same_account(): void
    {
        $accountId = $this->createAccountId();
        $user = $this->actingAsUser($accountId);

        $c1 = Contact::create(['account_id' => $user->account_id, 'name' => 'A', 'email' => 'a@example.com']);
        $c2 = Contact::create(['account_id' => $user->account_id, 'name' => 'B', 'email' => 'b@example.com']);

        $response = $this->post(route('contacts.bulk-delete'), [
            'ids' => [$c1->id, $c2->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('contacts', ['id' => $c1->id]);
        $this->assertDatabaseMissing('contacts', ['id' => $c2->id]);
    }

    public function test_bulk_delete_empty_selection_rejected(): void
    {
        $this->actingAsUser();

        $response = $this->post(route('contacts.bulk-delete'), [
            'ids' => [],
        ]);

        $response->assertSessionHasErrors(['ids']);
    }

    public function test_bulk_delete_cross_account_ids_not_deleted(): void
    {
        $accountA = $this->createAccountId();
        $accountB = $this->createAccountId();

        $this->actingAsUser($accountA);

        $foreignContact = Contact::create([
            'account_id' => $accountB,
            'name' => 'Foreign',
            'email' => 'foreign@example.com',
        ]);

        $response = $this->post(route('contacts.bulk-delete'), [
            'ids' => [$foreignContact->id],
        ]);

        $response->assertSessionHasErrors(['ids']);
        $this->assertDatabaseHas('contacts', ['id' => $foreignContact->id]);
    }

    public function test_bulk_delete_partial_valid_ids(): void
    {
        $accountA = $this->createAccountId();
        $accountB = $this->createAccountId();

        $user = $this->actingAsUser($accountA);

        $ownContact = Contact::create([
            'account_id' => $user->account_id,
            'name' => 'Own',
            'email' => 'own@example.com',
        ]);

        $foreignContact = Contact::create([
            'account_id' => $accountB,
            'name' => 'Foreign',
            'email' => 'foreign2@example.com',
        ]);

        $response = $this->post(route('contacts.bulk-delete'), [
            'ids' => [$ownContact->id, $foreignContact->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('contacts', ['id' => $ownContact->id]);
        $this->assertDatabaseHas('contacts', ['id' => $foreignContact->id]);
    }

    private function createAccountId(): int
    {
        return (int) DB::table('accounts')->insertGetId([
            'name' => 'Test Account '.uniqid(),
            'plan_id' => DB::table('plans')->value('id'),
            'owner_user_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
