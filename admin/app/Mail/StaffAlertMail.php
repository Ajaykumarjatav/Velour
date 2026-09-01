<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  list<string>  $lines
     */
    public function __construct(
        public string $staffName,
        public string $salonName,
        public string $subjectLine,
        public string $headline,
        public array $lines = [],
        public ?string $actionUrl = null,
        public string $actionLabel = 'Open EasyGrox',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff.alert',
            with: [
                'staffName'   => $this->staffName,
                'salonName'   => $this->salonName,
                'headline'    => $this->headline,
                'lines'       => $this->lines,
                'actionUrl'   => $this->actionUrl,
                'actionLabel' => $this->actionLabel,
                'subject'     => $this->subjectLine,
            ],
        );
    }
}
