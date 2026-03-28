<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class SubscriptionCreatedNotification extends Notification implements ShouldQueue, NotTenantAware
{
    use Queueable;

    public function __construct(
        public readonly string $planKey,
        public readonly bool   $onTrial = false
    ) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        $planName = ucfirst($this->planKey);
        $subject  = $this->onTrial
            ? "Your {$planName} trial has started 🎉"
            : "Welcome to Velour {$planName}!";

        return (new MailMessage())
            ->subject($subject)
            ->view('emails.billing.subscription-created', [
                'user'    => $notifiable,
                'plan'    => $planName,
                'onTrial' => $this->onTrial,
                'trialDays' => config("billing.plans.{$this->planKey}.trial_days", 0),
            ]);
    }
}
