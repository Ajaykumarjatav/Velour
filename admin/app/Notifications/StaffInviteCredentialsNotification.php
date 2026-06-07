<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffInviteCredentialsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $salonName,
        private readonly ?string $temporaryPassword = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('You are invited to ' . $this->salonName)
            ->greeting('Hello ' . ($notifiable->name ?: 'there') . ',')
            ->line('You have been invited to join ' . $this->salonName . ' on Velour. Open the sign-in page, then log in with your email and the temporary password below.')
            ->line('Email (login): ' . $notifiable->email);

        if ($this->temporaryPassword !== null) {
            $mail->line('Temporary password: ' . $this->temporaryPassword);
        }

        return $mail
            ->line('After you sign in, you will be asked to choose a new password before using the app. Your dashboard and menus follow the role assigned by your salon.')
            ->action('Open sign-in', route('login'));
    }
}
