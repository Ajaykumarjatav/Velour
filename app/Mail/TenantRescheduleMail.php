<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class TenantRescheduleMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public Carbon $originalStartsAt,
    ) {
        $this->appointment->loadMissing(['client', 'staff', 'services.service', 'salon']);
    }

    public function envelope(): Envelope
    {
        $client  = $this->appointment->client;
        $newDate = $this->appointment->starts_at->format('D j M, g:ia');

        return new Envelope(
            subject: "Booking rescheduled: {$client->first_name} {$client->last_name} — {$newDate}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointments.tenant-reschedule',
            with: [
                'appointment'      => $this->appointment,
                'salon'            => $this->appointment->salon,
                'originalStartsAt' => $this->originalStartsAt,
            ],
        );
    }
}
