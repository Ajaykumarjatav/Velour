<?php
namespace App\Notifications\Admin;
use App\Models\Salon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantSuspendedNotification extends Notification
{
    use Queueable;
    public function __construct(
        public readonly Salon $salon,
        public readonly string $reason,
        public readonly ?string $customerMessage = null
    ) {}
    public function via($notifiable): array { return ['mail']; }
    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Your {$this->salon->name} account has been suspended")
            ->greeting("Hello {$notifiable->name},")
            ->line("We're writing to let you know that your Velour account for **{$this->salon->name}** has been temporarily suspended.")
            ->line("**Reason:** " . ucwords(str_replace('_', ' ', $this->reason)));
        if ($this->customerMessage) {
            $mail->line($this->customerMessage);
        }
        return $mail
            ->line("If you believe this is an error or would like to discuss this, please contact our support team.")
            ->action('Contact Support', route('billing.dashboard'))
            ->line("We're here to help resolve any issues as quickly as possible.");
    }
}
