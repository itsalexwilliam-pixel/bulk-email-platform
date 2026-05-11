<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CampaignManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;
    private User $user;

    private function actingAsUser(): void
    {
        $this->account = Account::create([
            'name' => 'Test Account',
            'slug' => 'test-account',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'account_id' => $this->account->id,
            'role' => 'manager',
        ]);

        $this->actingAs($this->user);
    }

    public function test_create_campaign_as_draft(): void
    {
        $this->actingAsUser();

        $response = $this->post(route('campaigns.store'), [
            'name' => 'Spring Promo',
            'subject' => 'Big Offer',
            'body' => '<p>Hello subscribers</p>',
        ]);

        $response->assertRedirect(route('campaigns.index'));

        $this->assertDatabaseHas('campaigns', [
            'name' => 'Spring Promo',
            'subject' => 'Big Offer',
            'status' => 'draft',
        ]);
    }

    public function test_merge_contacts_and_groups_without_duplicates(): void
    {
        $this->actingAsUser();

        $manual = Contact::create(['account_id' => $this->account->id, 'name' => 'Manual', 'email' => 'manual@example.com']);
        $groupContact = Contact::create(['account_id' => $this->account->id, 'name' => 'Grouped', 'email' => 'grouped@example.com']);
        $overlap = Contact::create(['account_id' => $this->account->id, 'name' => 'Overlap', 'email' => 'overlap@example.com']);

        $group = Group::create(['account_id' => $this->account->id, 'name' => 'Leads']);
        $group->contacts()->sync([$groupContact->id, $overlap->id]);

        $response = $this->post(route('campaigns.store'), [
            'name' => 'Merge Test',
            'subject' => 'Merge Subject',
            'body' => '<p>Body</p>',
            'contact_ids' => [$manual->id, $groupContact->id, $overlap->id],
            'group_ids' => [$group->id],
        ]);

        $response->assertRedirect(route('campaigns.index'));

        $campaign = Campaign::where('name', 'Merge Test')->firstOrFail();

        $attached = $campaign->contacts()->pluck('contacts.id')->sort()->values()->all();
        $expected = collect([$manual->id, $groupContact->id, $overlap->id])->sort()->values()->all();

        $this->assertSame($expected, $attached);
        $this->assertDatabaseCount('campaign_contact', 3);
    }

    public function test_update_campaign_and_contact_assignments(): void
    {
        $this->actingAsUser();

        $contactA = Contact::create(['account_id' => $this->account->id, 'name' => 'A', 'email' => 'a@example.com']);
        $contactB = Contact::create(['account_id' => $this->account->id, 'name' => 'B', 'email' => 'b@example.com']);

        $campaign = Campaign::create([
            'account_id' => $this->account->id,
            'name' => 'Old Name',
            'subject' => 'Old Subject',
            'body' => '<p>Old</p>',
            'status' => 'draft',
        ]);

        $campaign->contacts()->sync([$contactA->id]);

        $response = $this->put(route('campaigns.update', $campaign), [
            'name' => 'New Name',
            'subject' => 'New Subject',
            'body' => '<p>New</p>',
            'contact_ids' => [$contactB->id],
        ]);

        $response->assertRedirect(route('campaigns.index'));

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'New Name',
            'subject' => 'New Subject',
            'status' => 'draft',
        ]);

        $this->assertDatabaseMissing('campaign_contact', [
            'campaign_id' => $campaign->id,
            'contact_id' => $contactA->id,
        ]);

        $this->assertDatabaseHas('campaign_contact', [
            'campaign_id' => $campaign->id,
            'contact_id' => $contactB->id,
        ]);
    }

    public function test_campaign_attachment_is_saved_and_updated(): void
    {
        $this->actingAsUser();
        Storage::fake('public');

        $createResponse = $this->post(route('campaigns.store'), [
            'name' => 'Attachment Campaign',
            'subject' => 'Attachment Subject',
            'body' => '<p>Body</p>',
            'attachment' => UploadedFile::fake()->create('offer.pdf', 200, 'application/pdf'),
        ]);

        $createResponse->assertRedirect(route('campaigns.index'));

        $campaign = Campaign::where('name', 'Attachment Campaign')->firstOrFail();

        $this->assertNotNull($campaign->attachment_path);
        $this->assertSame('offer.pdf', $campaign->attachment_name);
        Storage::disk('public')->assertExists($campaign->attachment_path);

        $oldPath = $campaign->attachment_path;

        $updateResponse = $this->put(route('campaigns.update', $campaign), [
            'name' => 'Attachment Campaign Updated',
            'subject' => 'Attachment Subject Updated',
            'body' => '<p>Body Updated</p>',
            'attachment' => UploadedFile::fake()->create('new-offer.pdf', 250, 'application/pdf'),
        ]);

        $updateResponse->assertRedirect(route('campaigns.index'));

        $campaign->refresh();

        $this->assertNotNull($campaign->attachment_path);
        $this->assertNotSame($oldPath, $campaign->attachment_path);
        Storage::disk('public')->assertExists($campaign->attachment_path);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_delete_campaign(): void
    {
        $this->actingAsUser();

        $campaign = Campaign::create([
            'account_id' => $this->account->id,
            'name' => 'Delete Me',
            'subject' => 'Delete Subject',
            'body' => '<p>Delete</p>',
            'status' => 'draft',
        ]);

        $response = $this->delete(route('campaigns.destroy', $campaign));

        $response->assertRedirect(route('campaigns.index'));
        $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
    }
}
