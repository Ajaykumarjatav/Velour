<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class PaymentFailedNotification extends Notification implements ShouldQueue, NotTenantAware
{
    use Queueable;

    public function __construct(
        public readonly float   $amount,
        public readonly ?Carbon $nextAttempt
    ) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Action required: Velour payment failed')
            ->view('emails.billing.payment-failed', [
                'user'        => $notifiable,
                'amount'      => $this->amount,
                'nextAttempt' => $this->nextAttempt,
                'portalUrl'   => route('billing.portal'),
            ]);
    }
}
