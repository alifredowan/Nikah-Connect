<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        if (config('app.env') === 'local') {
            session()->flash('dev_reset_url', $url);
        }

        return (new MailMessage)
            ->subject('Reset Your Nikah Connect Password')
            ->greeting("Assalamu Alaikum, {$notifiable->name}!")
            ->line('You are receiving this email because we received a password reset request for your Nikah Connect account.')
            ->action('Reset Password', $url)
            ->line('This password reset link will expire in 60 minutes.')
            ->line('If you did not request a password reset, no further action is required. Your account remains secure.')
            ->salutation('Warm regards, The Nikah Connect Team');
    }
}
