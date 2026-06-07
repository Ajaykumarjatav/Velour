<?php
namespace App\Notifications\Admin;
use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketReplyNotification extends Notification
{
    use Queueable;
    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly string $replyBody
    ) {}
    public function via($notifiable): array { return ['mail']; }
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[{$this->ticket->ticket_number}] Re: {$this->ticket->subject}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The Velour support team has replied to your ticket **{$this->ticket->ticket_number}**.")
            ->line("---")
            ->line(substr(strip_tags($this->replyBody), 0, 500))
            ->line("---")
            ->action('View Ticket', route('dashboard')) // adjust to actual ticket route when tenant-facing added
            ->line("Reply to this email or log in to your account to continue the conversation.");
    }
}
