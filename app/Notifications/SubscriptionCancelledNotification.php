<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class SubscriptionCancelledNotification extends Notification implements ShouldQueue, NotTenantAware
{
    use Queueable;

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Your Velour subscription has ended')
            ->view('emails.billing.subscription-cancelled', ['user' => $notifiable]);
    }
}
