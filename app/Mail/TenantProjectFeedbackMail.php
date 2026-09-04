<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\TenantProjectFeedback;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantProjectFeedbackMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly TenantProjectFeedback $feedback,
    ) {}

    public function envelope(): Envelope
    {
        $salonName = $this->feedback->salon?->name ?? 'Unknown store';

        return new Envelope(
            subject: "[EasyGrox] Tenant project feedback: {$salonName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ops.tenant-project-feedback',
            with: [
                'feedback' => $this->feedback,
                'user' => $this->feedback->user,
                'salon' => $this->feedback->salon,
            ],
        );
    }
}
