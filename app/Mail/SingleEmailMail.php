<?php

namespace App\Mail;

use App\Models\EmailQueue;
use App\Support\TracksEmailContent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class SingleEmailMail extends Mailable
{
    use Queueable, SerializesModels, TracksEmailContent;

    /**
     * @param array<int, array{path:string,name:string}> $attachmentsMeta
     */
    public function __construct(
        public EmailQueue $queueItem,
        public string $subjectLine,
        public string $htmlBody,
        public array $attachmentsMeta = []
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function build(): static
    {
        $normalizedHtml = $this->normalizeForEmailClient($this->htmlBody);
        $inlineReadyHtml = $this->inlineCssForEmailClients($normalizedHtml);

        $trackedHtml = $this->buildTrackedHtml(
            $inlineReadyHtml,
            $this->queueItem->id,
            false,
            null,
            [
                'utm_source' => $this->queueItem->utm_source,
                'utm_medium' => $this->queueItem->utm_medium,
                'utm_campaign' => $this->queueItem->utm_campaign,
                'utm_term' => $this->queueItem->utm_term,
                'utm_content' => $this->queueItem->utm_content,
            ]
        );

        return $this
            ->subject($this->subjectLine)
            ->html($trackedHtml);
    }

    private function normalizeForEmailClient(string $html): string
    {
        $trimmed = trim($html);

        if ($trimmed === '') {
            return $trimmed;
        }

        if (stripos($trimmed, '<html') !== false) {
            return $trimmed;
        }

        return '<!doctype html>'
            . '<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
            . '<body style="margin:0;padding:0;background:#ffffff;">'
            . '<div style="margin:0;padding:0;">' . $trimmed . '</div>'
            . '</body></html>';
    }

    private function inlineCssForEmailClients(string $html): string
    {
        if (trim($html) === '') {
            return $html;
        }

        try {
            $sanitized = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
            $inliner = new CssToInlineStyles();
            $inlined = $inliner->convert($sanitized);
            $inlined = $this->sanitizeUnsupportedInlineCss($inlined);

            // Basic fallbacks for clients that ignore class-based layout/CSS variables.
            $inlined = preg_replace(
                '/<body([^>]*)>/i',
                '<body$1 style="margin:0;padding:24px 14px 60px;background:#F5F2F8;color:#18182A;font-family:Arial,Helvetica,sans-serif;line-height:1.6;">',
                $inlined,
                1
            ) ?? $inlined;

            Log::info('Inline HTML sample', [
                'preview' => substr($inlined, 0, 1000),
            ]);

            return $inlined;
        } catch (\Throwable) {
            return $html;
        }
    }

    private function sanitizeUnsupportedInlineCss(string $html): string
    {
        return preg_replace_callback('/style\s*=\s*"([^"]*)"/i', function (array $matches): string {
            $style = $matches[1];

            $filtered = preg_replace('/\bdisplay\s*:\s*(flex|grid)\s*;?/i', '', $style) ?? $style;
            $filtered = preg_replace('/\bposition\s*:\s*[^;"]+;?/i', '', $filtered) ?? $filtered;
            $filtered = preg_replace('/\b[a-z-]+\s*:\s*var\([^)]+\)\s*;?/i', '', $filtered) ?? $filtered;
            $filtered = preg_replace('/\s{2,}/', ' ', trim($filtered)) ?? trim($filtered);
            $filtered = trim($filtered, " ;");

            return $filtered === '' ? '' : 'style="'.$filtered.'"';
        }, $html) ?? $html;
    }

    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->attachmentsMeta as $meta) {
            $path = $meta['path'] ?? '';
            $name = $meta['name'] ?? basename($path);

            if ($path !== '' && Storage::disk('local')->exists($path)) {
                $attachments[] = Attachment::fromPath(Storage::disk('local')->path($path))
                    ->as($name);
            }
        }

        return $attachments;
    }
}
