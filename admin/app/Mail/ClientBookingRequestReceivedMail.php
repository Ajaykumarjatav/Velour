<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Client-facing email when a pending booking request is submitted online.
 */
class ClientBookingRequestReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
        $this->appointment->loadMissing(['client', 'staff', 'services.service', 'salon']);
    }

    public function envelope(): Envelope
    {
        $reference = $this->appointment->reference ?? '';

        return new Envelope(
            subject: "Booking request received — {$reference}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointments.booking-request-received',
            with: [
                'appointment' => $this->appointment,
                'salon'       => $this->appointment->salon,
                'client'      => $this->appointment->client,
            ],
        );
    }
}
