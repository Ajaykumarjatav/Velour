<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Super-admin outreach email to a tenant owner (sent via support SMTP). */
class AdminTenantSupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $tenant,
        public readonly string $emailSubject,
        public readonly string $messageBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-tenant-support',
            with: [
                'tenant' => $this->tenant,
                'body' => $this->messageBody,
            ],
        );
    }
}
