<?php
namespace App\Notifications\Admin;
use App\Models\Salon;
use App\Models\TenantPlanOverride;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlanOverrideNotification extends Notification
{
    use Queueable;
    public function __construct(
        public readonly Salon $salon,
        public readonly TenantPlanOverride $override
    ) {}
    public function via($notifiable): array { return ['mail']; }
    public function toMail($notifiable): MailMessage
    {
        $planName = ucfirst($this->override->override_plan ?? 'upgraded');
        $expiry   = $this->override->expires_at
            ? "until " . $this->override->expires_at->format('d M Y')
            : "with no expiry date";
        return (new MailMessage)
            ->subject("Your Velour plan has been updated")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Great news — your account for **{$this->salon->name}** has been upgraded to the **{$planName}** plan {$expiry}.")
            ->line("You now have access to all features included in your new plan.")
            ->action('Explore your account', route('dashboard'))
            ->line("If you have any questions, our support team is always happy to help.");
    }
}
