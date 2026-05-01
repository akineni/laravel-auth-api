<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = config('frontend.login_url');

        return (new MailMessage)
            ->subject('Your Account Has Been Activated')
            ->greeting('Hello ' . ($notifiable->firstname ?? 'there') . ',')
            ->line('Great news: your account has been activated by an administrator.')
            ->line('You can now log in and access the platform.')
            ->action('Log In', $loginUrl)
            ->line('If you have any questions, feel free to contact support.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $type = NotificationTypeEnum::SECURITY_ALERT;

        return [
            'type'       => $type->value,
            'title'      => 'Account Activated',
            'message'    => 'Your account has been activated by an administrator.',
            'severity'   => 'info',
            'action_url' => config('frontend.login_url'),
            'meta'       => [
                'activated_at' => now()->toIso8601String(),
            ],
        ];
    }
}