<?php

namespace App\Mail;

use App\Models\Salon;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OpsSignupAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $event,
        public readonly User $user,
        public readonly Salon $salon,
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->event === 'new_store' ? 'New store' : 'New user';

        return new Envelope(
            subject: "[EasyGrox] {$label}: {$this->salon->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ops.signup-alert',
            with: [
                'event' => $this->event,
                'user' => $this->user,
                'salon' => $this->salon,
            ],
        );
    }
}
