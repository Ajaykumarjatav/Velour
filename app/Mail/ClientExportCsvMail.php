<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientExportCsvMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $salonName,
        public string $fileName,
        public string $csvContent,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Client export - ' . $this->salonName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.clients.export-csv',
            with: [
                'salonName' => $this->salonName,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->csvContent, $this->fileName)
                ->withMime('text/csv'),
        ];
    }
}

