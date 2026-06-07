<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantCancellationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
        $this->appointment->loadMissing(['client', 'staff', 'services.service', 'salon']);
    }

    public function envelope(): Envelope
    {
        $client = $this->appointment->client;
        $date   = $this->appointment->starts_at->format('D j M, g:ia');

        return new Envelope(
            subject: "Booking cancelled: {$client->first_name} {$client->last_name} — {$date}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointments.tenant-cancellation',
            with: [
                'appointment' => $this->appointment,
                'salon'       => $this->appointment->salon,
            ],
        );
    }
}
