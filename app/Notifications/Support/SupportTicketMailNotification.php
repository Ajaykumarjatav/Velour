<?php

namespace App\Notifications\Support;

use App\Models\SupportTicket;
use App\Support\MailAssets;
use App\Support\PurposeMail;
use App\Support\SupportTicketUrls;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

class SupportTicketMailNotification extends Notification
{
    use Queueable;

    /**
     * @param  'created'|'updated'|'replied'|'status'  $event
     * @param  'tenant'|'admin'  $audience
     */
    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly string $event,
        public readonly string $audience,
        public readonly string $summary,
        public readonly ?string $excerpt = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $this->ticket->loadMissing(['salon', 'user']);
        $number = $this->ticket->ticket_number;
        $subjectLine = $this->ticket->subject;
        $name = trim((string) (data_get($notifiable, 'name') ?? 'there'));
        $salonName = $this->ticket->salon?->name ?? 'your store';

        $subject = match ($this->event) {
            'created' => "[{$number}] Support ticket created: {$subjectLine}",
            'replied' => "[{$number}] New reply on: {$subjectLine}",
            'status' => "[{$number}] Ticket {$this->ticket->status}: {$subjectLine}",
            default => "[{$number}] Ticket updated: {$subjectLine}",
        };

        $greetingLine = $name !== '' && $name !== 'there'
            ? "Hello {$name},"
            : 'Hello,';

        $url = $this->audience === 'admin'
            ? SupportTicketUrls::adminShow($this->ticket)
            : SupportTicketUrls::tenantShow($this->ticket);

        $heading = match ($this->event) {
            'created' => $this->audience === 'admin' ? 'New support ticket' : 'We received your ticket',
            'replied' => $this->audience === 'admin' ? 'Tenant replied' : 'Support replied',
            'status' => 'Ticket status updated',
            default => 'Ticket update',
        };

        $ctaLabel = $this->audience === 'admin' ? 'Open in admin panel' : 'View your ticket';

        $logoPath = public_path('images/easygrox-logo-light.png');
        $embedLogo = is_file($logoPath);
        $logoUrl = $embedLogo ? 'cid:easygrox-logo' : MailAssets::logoUrl();

        $mail = (new MailMessage)
            ->subject($subject)
            ->view('emails.support.ticket', [
                'emailSubject' => $subject,
                'heading' => $heading,
                'greetingLine' => $greetingLine,
                'summary' => $this->summary,
                'ticket' => $this->ticket,
                'salonName' => $salonName,
                'excerpt' => $this->excerpt,
                'url' => $url,
                'ctaLabel' => $ctaLabel,
                'logoUrl' => $logoUrl,
            ]);

        if ($embedLogo) {
            $mail->withSymfonyMessage(function (Email $message) use ($logoPath) {
                $message->embedFromPath($logoPath, 'easygrox-logo', 'image/png');
            });
        }

        return PurposeMail::configureMailMessage(PurposeMail::SUPPORT, $mail);
    }
}
