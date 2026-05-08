<?php

namespace App\Http\Controllers;

use App\Models\EmailClick;
use App\Models\EmailOpen;
use App\Models\EmailQueue;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    public function open(Request $request, int $id)
    {
        $queue = EmailQueue::find($id);
        if (!$queue) {
            abort(404);
        }

        $exists = EmailOpen::where('email_queue_id', $queue->id)
            ->where('ip_address', $request->ip())
            ->where('created_at', '>=', now()->subMinute())
            ->exists();

        if (!$exists) {
            EmailOpen::create([
                'email_queue_id' => $queue->id,
                'opened_at'      => now(),
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'created_at'     => now(),
            ]);

            $this->fireWebhook($queue, 'open', [
                'event'          => 'open',
                'email'          => $queue->email,
                'email_queue_id' => $queue->id,
                'campaign_id'    => $queue->campaign_id,
                'contact_id'     => $queue->contact_id,
                'opened_at'      => now()->toIso8601String(),
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);
        }

        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');

        return response($pixel, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function click(Request $request, int $id)
    {
        $queue = EmailQueue::find($id);
        if (!$queue) {
            abort(404);
        }

        $encodedUrl = $request->query('url');
        if (!$encodedUrl) {
            abort(422, 'Missing url parameter');
        }

        $decodedUrl = urldecode($encodedUrl);
        if (!filter_var($decodedUrl, FILTER_VALIDATE_URL)) {
            abort(422, 'Invalid url');
        }

        EmailClick::create([
            'email_queue_id' => $queue->id,
            'url'            => $decodedUrl,
            'clicked_at'     => now(),
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'created_at'     => now(),
        ]);

        $this->fireWebhook($queue, 'click', [
            'event'          => 'click',
            'email'          => $queue->email,
            'email_queue_id' => $queue->id,
            'campaign_id'    => $queue->campaign_id,
            'contact_id'     => $queue->contact_id,
            'url'            => $decodedUrl,
            'clicked_at'     => now()->toIso8601String(),
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
        ]);

        return redirect()->away($decodedUrl);
    }

    private function fireWebhook(EmailQueue $queue, string $event, array $payload): void
    {
        try {
            $account = Account::find($queue->account_id);
            if (!$account || empty($account->webhook_url)) {
                return;
            }

            $url = $account->webhook_url;
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return;
            }

            Http::timeout(5)->post($url, $payload);
        } catch (\Throwable $e) {
            Log::warning("Webhook delivery failed [{$event}]: " . $e->getMessage());
        }
    }
}
