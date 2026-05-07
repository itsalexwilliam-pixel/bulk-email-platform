<?php

namespace App\Http\Controllers;

use App\Models\EmailClick;
use App\Models\EmailOpen;
use App\Models\EmailQueue;
use Illuminate\Http\Request;

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
                'opened_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
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
            'url' => $decodedUrl,
            'clicked_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->away($decodedUrl);
    }
}
