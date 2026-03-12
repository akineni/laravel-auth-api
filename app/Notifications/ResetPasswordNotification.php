<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected readonly string $resetUrl
    ) {}

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
        $expiryMinutes = config('auth.passwords.users.expire');

        return (new MailMessage)
            ->subject('Reset Your Password')
            ->greeting("Hello {$notifiable->firstname},")
            ->line('You requested to reset your password.')
            ->line("This password reset link will expire in {$expiryMinutes} minutes.")
            ->action('Reset Password', $this->resetUrl)
            ->line('If you did not request this, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reset_url' => $this->resetUrl,
        ];
    }
}