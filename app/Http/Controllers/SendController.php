<?php

namespace App\Http\Controllers;

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
            }
        }

        // If rows entered queue, mark as sending so UI reflects live pipeline activity.
        // Keep enum-safe fallback to scheduled when nothing new was queued.
        $campaign->update(['status' => $inserted > 0 ? 'sending' : 'scheduled']);

        return redirect()->route('campaigns.index')
            ->with('success', $inserted > 0
                ? "Queued {$inserted} email(s). Sending started."
                : "No new recipients were queued. Campaign remains scheduled.");
    }
}
