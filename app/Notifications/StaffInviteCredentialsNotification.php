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
            ->line('You have been added as a staff member in ' . $this->salonName . '.')
            ->line('Login URL: ' . route('login'))
            ->line('User ID: ' . $notifiable->email);

        if ($this->temporaryPassword !== null) {
            $mail->line('One-time password: ' . $this->temporaryPassword);
        }

        return $mail
            ->line('For security, you must change your password immediately after login.')
            ->action('Sign in', route('login'));
    }
}
