<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ContactManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        return $user;
    }

    public function test_create_contact_valid_and_assign_group(): void
    {
        $this->actingAsUser();
        $group = Group::create(['name' => 'Leads']);

        $response = $this->post(route('contacts.store'), [
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'groups' => [$group->id],
        ]);

        $response->assertRedirect(route('contacts.index'));
        $this->assertDatabaseHas('contacts', [
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
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
            'email' => 'bobby@example.com',
            'groups' => [$groupB->id],
        ]);

        $response->assertRedirect(route('contacts.index'));

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'name' => 'Bobby',
            'email' => 'bobby@example.com',
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
}
