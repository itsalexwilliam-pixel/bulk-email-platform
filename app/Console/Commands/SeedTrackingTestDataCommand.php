<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\EmailQueue;
use Illuminate\Console\Command;

class SeedTrackingTestDataCommand extends Command
{
    protected $signature = 'tracking:seed-test-data';
    protected $description = 'Seed minimal campaign/contact/email_queue data for tracking endpoint tests';

    public function handle()
    {
        $email = 'tracktest@example.com';

        $contact = Contact::firstOrCreate(
            ['email' => $email],
            ['name' => 'Track Test']
        );

        $campaign = Campaign::create([
            'name' => 'Track Campaign',
            'subject' => 'Tracking Subject',
            'body' => '<html><body><p>Hello</p><a href="https://example.com">Example</a></body></html>',
        ]);

        $queue = EmailQueue::create([
            'campaign_id' => $campaign->id,
            'contact_id' => $contact->id,
            'email' => $contact->email,
            'status' => 'pending',
            'attempts' => 0,
        ]);

        $this->info('tracking_queue_id=' . $queue->id);

        return self::SUCCESS;
    }
}
