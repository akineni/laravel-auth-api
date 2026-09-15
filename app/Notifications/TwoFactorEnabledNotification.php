<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorEnabledNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
        $type = NotificationTypeEnum::TWO_FA_ENABLED;

        return (new MailMessage)
            ->subject($type->label())
            ->greeting('Hello ' . ($notifiable->firstname ?? 'there') . ',')
            ->line('Two-factor authentication was enabled on your account using an authenticator app.')
            ->line('If this was not you, please contact support immediately.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $type = NotificationTypeEnum::TWO_FA_ENABLED;

        return [
            'type' => $type->value,
            'title' => $type->label(),
            'message' => 'Two-factor authentication was enabled on your account.',
            'severity' => $type->severity(),
            'action_url' => null,
            'meta' => [
                'enabled_at' => now()->toIso8601String(),
            ],
        ];
    }
}
