<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): void
    {
        $this->actingAs(User::factory()->create());
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

        $manual = Contact::create(['name' => 'Manual', 'email' => 'manual@example.com']);
        $groupContact = Contact::create(['name' => 'Grouped', 'email' => 'grouped@example.com']);
        $overlap = Contact::create(['name' => 'Overlap', 'email' => 'overlap@example.com']);

        $group = Group::create(['name' => 'Leads']);
        $group->contacts()->sync([$groupContact->id, $overlap->id]);

        $response = $this->post(route('campaigns.store'), [
            'name' => 'Merge Test',
            'subject' => 'Merge Subject',
            'body' => '<p>Body</p>',
            'contact_ids' => [$manual->id, $overlap->id],
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

        $contactA = Contact::create(['name' => 'A', 'email' => 'a@example.com']);
        $contactB = Contact::create(['name' => 'B', 'email' => 'b@example.com']);

        $campaign = Campaign::create([
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

    public function test_delete_campaign(): void
    {
        $this->actingAsUser();

        $campaign = Campaign::create([
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
