<?php
namespace App\Notifications\Admin;
use App\Models\Salon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantUnsuspendedNotification extends Notification
{
    use Queueable;
    public function __construct(
        public readonly Salon $salon,
        public readonly ?string $message = null
    ) {}
    public function via($notifiable): array { return ['mail']; }
    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Your {$this->salon->name} account has been reinstated")
            ->greeting("Good news, {$notifiable->name}!")
            ->line("Your Velour account for **{$this->salon->name}** has been fully reinstated and is now active again.");
        if ($this->message) { $mail->line($this->message); }
        return $mail
            ->action('Go to Dashboard', route('dashboard'))
            ->line("Thank you for your patience.");
    }
}
