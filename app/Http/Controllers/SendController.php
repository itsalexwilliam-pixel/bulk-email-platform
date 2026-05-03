<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCampaignQueueJob;
use App\Models\Campaign;
use App\Models\EmailQueue;

class SendController extends Controller
{
    public function sendNow(Campaign $campaign)
    {
        $contacts = $campaign->contacts()->get(['contacts.id', 'contacts.email']);

        $inserted = 0;
        foreach ($contacts as $contact) {
            $queue = EmailQueue::firstOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'contact_id' => $contact->id,
                ],
                [
                    'email' => $contact->email,
                    'status' => 'pending',
                    'attempts' => 0,
                ]
            );

            if ($queue->wasRecentlyCreated) {
                $inserted++;
            } elseif (in_array($queue->status, ['failed', 'paused'], true) && (int) $queue->attempts < 3) {
                $queue->update([
                    'status' => 'pending',
                    'last_error' => null,
                ]);
            }
        }

        $hasSendableQueue = EmailQueue::where('campaign_id', $campaign->id)
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere(function ($q) {
                        $q->where('status', 'failed')->where('attempts', '<', 3);
                    });
            })
            ->exists();

        $campaign->update(['status' => $hasSendableQueue ? 'sending' : 'scheduled']);

        $workerTriggered = false;
        if ($hasSendableQueue) {
            ProcessCampaignQueueJob::dispatch($campaign->id);
            $workerTriggered = true;
        }

        $message = $inserted > 0
            ? "Queued {$inserted} email(s). Sending started."
            : ($hasSendableQueue
                ? 'Existing queued recipients found. Sending resumed.'
                : 'No sendable recipients in queue. Campaign remains scheduled.');

        if ($workerTriggered) {
            $message .= ' Background worker triggered.';
        }

        return redirect()->route('campaigns.index')->with('success', $message);
    }

    public function pause(Campaign $campaign)
    {
        if ($campaign->status !== 'sending') {
            return redirect()->route('campaigns.index')->withErrors([
                'campaign_pause' => 'Only sending campaigns can be paused.',
            ]);
        }

        $campaign->update(['status' => 'paused']);

        return redirect()->route('campaigns.index')->with('success', 'Campaign paused successfully.');
    }

    public function resume(Campaign $campaign)
    {
        $hasSendableQueue = EmailQueue::where('campaign_id', $campaign->id)
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere(function ($q) {
                        $q->where('status', 'failed')->where('attempts', '<', 3);
                    });
            })
            ->exists();

        if (!$hasSendableQueue) {
            return redirect()->route('campaigns.index')->withErrors([
                'campaign_resume' => 'No sendable recipients available to resume this campaign.',
            ]);
        }

        $campaign->update(['status' => 'sending']);

        return redirect()->route('campaigns.index')->with('success', 'Campaign resumed successfully.');
    }
}
