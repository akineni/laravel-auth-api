<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorDisabledNotification extends Notification implements ShouldQueue
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
        $type = NotificationTypeEnum::TWO_FA_DISABLED;

        return (new MailMessage)
            ->subject($type->label())
            ->greeting('Hello ' . ($notifiable->firstname ?? 'there') . ',')
            ->line('Two-factor authentication was disabled on your account.')
            ->line('Your account is now less protected. If this was not you, please contact support immediately.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $type = NotificationTypeEnum::TWO_FA_DISABLED;

        return [
            'type' => $type->value,
            'title' => $type->label(),
            'message' => 'Two-factor authentication was disabled on your account.',
            'severity' => $type->severity(),
            'action_url' => null,
            'meta' => [
                'disabled_at' => now()->toIso8601String(),
            ],
        ];
    }
}
