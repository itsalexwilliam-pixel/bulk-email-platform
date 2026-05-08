<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\EmailBounce;
use App\Models\Unsubscribe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BounceController extends Controller
{
    /**
     * SES SNS webhook — receives bounce/complaint notifications from AWS.
     * URL: POST /webhooks/ses-bounce  (no auth middleware)
     */
    public function sesBounce(Request $request)
    {
        $raw = $request->getContent();
        $payload = json_decode($raw, true);

        if (!$payload) {
            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        // SNS subscription confirmation
        if (($payload['Type'] ?? '') === 'SubscriptionConfirmation') {
            $url = $payload['SubscribeURL'] ?? null;
            if ($url) {
                \Illuminate\Support\Facades\Http::get($url);
            }
            return response()->json(['ok' => true]);
        }

        // Notification
        if (($payload['Type'] ?? '') !== 'Notification') {
            return response()->json(['ok' => true]);
        }

        $message = json_decode($payload['Message'] ?? '{}', true);
        $notifType = $message['notificationType'] ?? $message['eventType'] ?? '';

        if ($notifType === 'Bounce') {
            $this->handleBouncedAddresses($message['bounce'] ?? []);
        } elseif ($notifType === 'Complaint') {
            $this->handleComplaintAddresses($message['complaint'] ?? []);
        }

        return response()->json(['ok' => true]);
    }

    private function handleBouncedAddresses(array $bounce): void
    {
        $bounceType    = strtolower($bounce['bounceType'] ?? 'undetermined');
        $bounceSubtype = $bounce['bounceSubType'] ?? null;
        $recipients    = $bounce['bouncedRecipients'] ?? [];

        foreach ($recipients as $recipient) {
            $email      = strtolower(trim($recipient['emailAddress'] ?? ''));
            $diagnostic = $recipient['diagnosticCode'] ?? null;

            if (!$email) continue;

            // Record the bounce
            EmailBounce::firstOrCreate(
                ['email' => $email, 'bounce_type' => $bounceType === 'permanent' ? 'hard' : 'soft'],
                [
                    'bounce_subtype' => $bounceSubtype,
                    'diagnostic'     => $diagnostic,
                    'source'         => 'ses',
                    'bounced_at'     => now(),
                ]
            );

            // Hard bounce → mark contact + auto-unsubscribe
            if (in_array($bounceType, ['permanent', 'hard'], true)) {
                Contact::where('email', $email)->update(['is_bounced' => true]);

                Unsubscribe::updateOrCreate(
                    ['email' => $email],
                    ['unsubscribed_at' => now(), 'created_at' => now()]
                );

                Log::info("Hard bounce — auto-unsubscribed: {$email}");
            }
        }
    }

    private function handleComplaintAddresses(array $complaint): void
    {
        $recipients = $complaint['complainedRecipients'] ?? [];

        foreach ($recipients as $recipient) {
            $email = strtolower(trim($recipient['emailAddress'] ?? ''));
            if (!$email) continue;

            EmailBounce::firstOrCreate(
                ['email' => $email, 'bounce_type' => 'complaint'],
                ['source' => 'ses', 'bounced_at' => now()]
            );

            // Complaint → auto-unsubscribe
            Unsubscribe::updateOrCreate(
                ['email' => $email],
                ['unsubscribed_at' => now(), 'created_at' => now()]
            );

            Log::info("Complaint received — auto-unsubscribed: {$email}");
        }
    }

    /**
     * Manual: mark a contact as bounced from the UI.
     */
    public function markBounced(Request $request, Contact $contact)
    {
        $accountId = $this->getAccountId($request);
        abort_if((int) $contact->account_id !== $accountId, 403);

        $contact->update(['is_bounced' => true]);

        EmailBounce::firstOrCreate(
            ['email' => strtolower($contact->email), 'bounce_type' => 'hard'],
            ['source' => 'manual', 'account_id' => $accountId, 'bounced_at' => now()]
        );

        // Auto-unsubscribe
        Unsubscribe::updateOrCreate(
            ['email' => strtolower($contact->email)],
            ['contact_id' => $contact->id, 'unsubscribed_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', "Contact marked as bounced and unsubscribed.");
    }

    /**
     * Manual: clear bounce flag from a contact.
     */
    public function clearBounced(Request $request, Contact $contact)
    {
        $accountId = $this->getAccountId($request);
        abort_if((int) $contact->account_id !== $accountId, 403);

        $contact->update(['is_bounced' => false]);

        return back()->with('success', "Bounce flag cleared.");
    }

    /**
     * Bounces list page (admin view).
     */
    public function index(Request $request)
    {
        $accountId = $this->getAccountId($request);

        $bouncedContacts = Contact::where('account_id', $accountId)
            ->where('is_bounced', true)
            ->with('tags')
            ->latest()
            ->paginate(25);

        $totalBounces   = EmailBounce::count();
        $hardBounces    = EmailBounce::where('bounce_type', 'hard')->count();
        $softBounces    = EmailBounce::where('bounce_type', 'soft')->count();
        $complaints     = EmailBounce::where('bounce_type', 'complaint')->count();

        return view('bounces.index', compact(
            'bouncedContacts', 'totalBounces', 'hardBounces', 'softBounces', 'complaints'
        ));
    }
}
