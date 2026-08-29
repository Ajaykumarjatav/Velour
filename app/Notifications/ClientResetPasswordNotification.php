<?php

namespace App\Notifications;

use App\Support\PurposeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $token,
        public string $salonSlug,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = \App\Support\StorefrontUrl::publicAppUrl()
            .'/s/'.$this->salonSlug
            .'#reset-password?token='.$this->token
            .'&email='.urlencode($notifiable->email);

        return PurposeMail::configureMailMessage(PurposeMail::AUTH, (new MailMessage)
            ->subject('Reset your password')
            ->line('You requested a password reset for your client account.')
            ->action('Reset password', $url)
            ->line('This link expires in 60 minutes.'));
    }
}
