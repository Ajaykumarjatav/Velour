<?php

namespace App\Mail;

use App\Models\Salon;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User  $user,
        public readonly Salon $salon,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Welcome to Velour — {$this->salon->name} is live 🎉",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: ['user' => $this->user, 'salon' => $this->salon],
        );
    }
}
