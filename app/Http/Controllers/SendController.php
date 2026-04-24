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

        $campaign->update(['status' => 'queued']);

        return redirect()->route('campaigns.index')
            ->with('success', "Queued {$inserted} email(s) for sending.");
    }
}
