<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientReviewRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $salonName,
        public string $clientName,
        public string $reviewUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "We value your feedback — {$this->salonName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reviews.request',
            with: [
                'salonName' => $this->salonName,
                'clientName' => $this->clientName,
                'reviewUrl' => $this->reviewUrl,
            ],
        );
    }
}

