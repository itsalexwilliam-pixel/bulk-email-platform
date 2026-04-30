<?php

namespace App\Mail;

use App\Models\EmailQueue;
use App\Support\TracksEmailContent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

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

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildTrackedHtml(
                $this->htmlBody,
                $this->queueItem->id,
                false,
                null
            )
        );
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
