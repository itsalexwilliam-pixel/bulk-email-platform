<?php

namespace App\Mail;

use App\Models\Contact;
use App\Models\DripStep;
use App\Support\EmailHtmlPreprocessor;
use App\Support\TracksEmailContent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DripMail extends Mailable
{
    use Queueable, SerializesModels, TracksEmailContent;

    public function __construct(
        public DripStep $step,
        public Contact $contact,
        public int $queueId
    ) {
    }

    public function envelope(): Envelope
    {
        $unsubscribeUrl = route('unsubscribe', ['email' => rawurlencode($this->contact->email)]);

        return new Envelope(
            subject: $this->replaceMergeTags($this->step->subject),
            using: [
                function (\Symfony\Component\Mime\Email $email) use ($unsubscribeUrl) {
                    $email->getHeaders()
                        ->addTextHeader('List-Unsubscribe', '<' . $unsubscribeUrl . '>')
                        ->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
                },
            ]
        );
    }

    public function content(): Content
    {
        $body = $this->replaceMergeTags($this->step->body);
        $processed = EmailHtmlPreprocessor::preprocess($body);
        $normalized = $this->normalizeForEmailClient($processed);
        $inlined = $this->inlineCssForEmailClients($normalized);

        return new Content(
            htmlString: $this->buildTrackedHtml($inlined, $this->queueId, true, $this->contact->email)
        );
    }

    private function replaceMergeTags(string $text): string
    {
        return str_replace(
            ['{{First Name}}', '{{Name}}', '{{Email}}', '{{Business Name}}', '{{Website}}'],
            [$this->contact->name ?? '', $this->contact->name ?? '', $this->contact->email ?? '', $this->contact->business_name ?? '', $this->contact->website ?? ''],
            $text
        );
    }

    private function normalizeForEmailClient(string $html): string
    {
        $trimmed = trim($html);
        if ($trimmed === '' || stripos($trimmed, '<html') !== false) {
            return $trimmed;
        }
        return '<!doctype html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>'
            . '<body style="margin:0;padding:0;background:#ffffff;"><div>' . $trimmed . '</div></body></html>';
    }

    private function inlineCssForEmailClients(string $html): string
    {
        if (trim($html) === '') return $html;
        try {
            $sanitized = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
            $inliner = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();
            $inlined = $inliner->convert($sanitized);
            return preg_replace('/<body([^>]*)>/i',
                '<body$1 style="margin:0;padding:24px 14px 60px;background:#F5F2F8;color:#18182A;font-family:Arial,Helvetica,sans-serif;line-height:1.6;">',
                $inlined, 1) ?? $inlined;
        } catch (\Throwable) {
            return $html;
        }
    }

    public function attachments(): array
    {
        return [];
    }
}
