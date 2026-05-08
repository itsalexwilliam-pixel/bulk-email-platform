<?php

namespace App\Console\Commands;

use App\Jobs\ProcessCampaignQueueJob;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\EmailQueue;
use Illuminate\Console\Command;

class DispatchScheduledCampaignsCommand extends Command
{
    protected $signature = 'campaigns:dispatch-scheduled';
    protected $description = 'Auto-send campaigns whose scheduled_at time has passed';

    public function handle()
    {
        $due = Campaign::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled campaigns due.');
            return self::SUCCESS;
        }

        $this->info("Found {$due->count()} scheduled campaign(s) due.");

        foreach ($due as $campaign) {
            $this->line("Dispatching campaign #{$campaign->id}: {$campaign->name}");

            $accountId = (int) $campaign->account_id;
            $contacts  = $campaign->contacts()->get(['contacts.id', 'contacts.email']);

            $inserted = 0;
            foreach ($contacts as $contact) {
                $queue = EmailQueue::firstOrCreate(
                    ['campaign_id' => $campaign->id, 'contact_id' => $contact->id],
                    [
                        'account_id'    => $accountId,
                        'email'         => $contact->email,
                        'type'          => 'campaign',
                        'subject'       => $campaign->subject,
                        'body'          => $campaign->body,
                        'body_snapshot' => $campaign->body,
                        'status'        => 'pending',
                        'attempts'      => 0,
                    ]
                );
                if ($queue->wasRecentlyCreated) $inserted++;
            }

            $hasSendable = EmailQueue::where('campaign_id', $campaign->id)
                ->where(function ($q) {
                    $q->where('status', 'pending')
                      ->orWhere(function ($q2) {
                          $q2->where('status', 'failed')->where('attempts', '<', 3);
                      });
                })
                ->exists();

            $campaign->update(['status' => $hasSendable ? 'sending' : 'scheduled']);

            if ($hasSendable) {
                ProcessCampaignQueueJob::dispatch($campaign->id);
                $this->info("Campaign #{$campaign->id} queued {$inserted} email(s). Worker dispatched.");
            } else {
                $this->warn("Campaign #{$campaign->id} has no sendable recipients.");
            }
        }

        return self::SUCCESS;
    }
}
