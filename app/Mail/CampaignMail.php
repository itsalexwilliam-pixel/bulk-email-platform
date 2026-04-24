<?php

namespace App\Mail;

use App\Models\Campaign;
use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Campaign $campaign,
        public Contact $contact,
        public int $queueId
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildTrackedHtml($this->campaign->body)
        );
    }

    private function buildTrackedHtml(string $html): string
    {
        $html = $this->rewriteLinksForTracking($html);

        $openUrl = route('track.open', ['id' => $this->queueId]);
        $pixel = '<img src="' . e($openUrl) . '" alt="" width="1" height="1" style="display:none;" />';

        $unsubscribeUrl = route('unsubscribe', ['email' => rawurlencode($this->contact->email)]);
        $unsubscribeHtml = '<p style="margin-top:16px;font-size:12px;color:#6b7280;">'
            . '<a href="' . e($unsubscribeUrl) . '">Unsubscribe</a>'
            . '</p>';

        $injection = $unsubscribeHtml . $pixel;

        if (stripos($html, '</body>') !== false) {
            return preg_replace('/<\/body>/i', $injection . '</body>', $html, 1) ?? ($html . $injection);
        }

        return $html . $injection;
    }

    private function rewriteLinksForTracking(string $html): string
    {
        return preg_replace_callback('/<a\b[^>]*\bhref=(["\'])(.*?)\1[^>]*>/i', function ($matches) {
            $fullTag = $matches[0];
            $href = trim($matches[2]);

            if ($href === '' || str_starts_with($href, '#') || str_starts_with(strtolower($href), 'javascript:')) {
                return $fullTag;
            }

            if (!filter_var($href, FILTER_VALIDATE_URL)) {
                return $fullTag;
            }

            $trackedUrl = route('track.click', [
                'id' => $this->queueId,
                'url' => urlencode($href),
            ]);

            return str_replace($matches[2], e($trackedUrl), $fullTag);
        }, $html) ?? $html;
    }
}
