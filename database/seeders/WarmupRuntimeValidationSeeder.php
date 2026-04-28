<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\EmailQueue;
use App\Models\Unsubscribe;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarmupRuntimeValidationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1) Upsert campaigns by name
        $warmupCampaign = Campaign::updateOrCreate(
            ['name' => 'Warmup Runtime Campaign'],
            [
                'subject' => 'Warmup Runtime Subject',
                'body' => 'Warmup runtime body',
                'status' => 'scheduled',
                'warmup_enabled' => true,
                'warmup_day' => 1,
                'warmup_started_at' => $now,
            ]
        );

        $noWarmupCampaign = Campaign::updateOrCreate(
            ['name' => 'No Warmup Runtime Campaign'],
            [
                'subject' => 'No Warmup Runtime Subject',
                'body' => 'No warmup runtime body',
                'status' => 'scheduled',
                'warmup_enabled' => false,
            ]
        );

        $warmupContacts = [];
        $noWarmupContacts = [];

        // 2) Deterministic contacts (120 each), firstOrCreate by email
        for ($i = 1; $i <= 120; $i++) {
            $contact = Contact::firstOrCreate(
                ['email' => "warmup_test_{$i}@example.com"],
                ['name' => "Warmup Test {$i}"]
            );
            $warmupContacts[] = $contact->id;
        }

        for ($i = 1; $i <= 120; $i++) {
            $contact = Contact::firstOrCreate(
                ['email' => "nowarmup_test_{$i}@example.com"],
                ['name' => "NoWarmup Test {$i}"]
            );
            $noWarmupContacts[] = $contact->id;
        }

        // 3) Ensure NOT unsubscribed
        Unsubscribe::whereIn('email', array_map(fn ($i) => "warmup_test_{$i}@example.com", range(1, 120)))->delete();
        Unsubscribe::whereIn('email', array_map(fn ($i) => "nowarmup_test_{$i}@example.com", range(1, 120)))->delete();

        // 4) Attach contacts to campaigns (many-to-many)
        $warmupCampaign->contacts()->syncWithoutDetaching($warmupContacts);
        $noWarmupCampaign->contacts()->syncWithoutDetaching($noWarmupContacts);

        // 5) Queue rows: firstOrCreate by (campaign_id, contact_id), set pending/attempts
        foreach ($warmupContacts as $contactId) {
            $contact = Contact::find($contactId);

            $queue = EmailQueue::firstOrCreate(
                [
                    'campaign_id' => $warmupCampaign->id,
                    'contact_id' => $contactId,
                ],
                [
                    'email' => $contact->email,
                    'status' => 'pending',
                    'attempts' => 0,
                ]
            );

            if ($queue->status !== 'pending') {
                $queue->status = 'pending';
                $queue->attempts = 0;
                $queue->last_error = null;
                $queue->save();
            }
        }

        foreach ($noWarmupContacts as $contactId) {
            $contact = Contact::find($contactId);

            $queue = EmailQueue::firstOrCreate(
                [
                    'campaign_id' => $noWarmupCampaign->id,
                    'contact_id' => $contactId,
                ],
                [
                    'email' => $contact->email,
                    'status' => 'pending',
                    'attempts' => 0,
                ]
            );

            if ($queue->status !== 'pending') {
                $queue->status = 'pending';
                $queue->attempts = 0;
                $queue->last_error = null;
                $queue->save();
            }
        }

        // 6) Pre-check counts
        $warmupPending = EmailQueue::where('campaign_id', $warmupCampaign->id)->where('status', 'pending')->count();
        $noWarmupPending = EmailQueue::where('campaign_id', $noWarmupCampaign->id)->where('status', 'pending')->count();

        $this->command?->info("Warmup campaign #{$warmupCampaign->id} pending rows: {$warmupPending}");
        $this->command?->info("No-warmup campaign #{$noWarmupCampaign->id} pending rows: {$noWarmupPending}");
    }
}
