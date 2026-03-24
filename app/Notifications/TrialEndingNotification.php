<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class TrialEndingNotification extends Notification implements ShouldQueue, NotTenantAware
{
    use Queueable;

    public function __construct(public readonly Carbon $trialEndsAt) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Your Velour trial ends in 3 days')
            ->view('emails.billing.trial-ending', [
                'user'         => $notifiable,
                'trialEndsAt'  => $this->trialEndsAt,
                'billingUrl'   => route('billing.plans'),
            ]);
    }
}
