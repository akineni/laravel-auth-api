<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountDeactivatedNotification extends Notification implements ShouldQueue
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
        $type = NotificationTypeEnum::ACCOUNT_DEACTIVATED;

        return (new MailMessage)
            ->subject($type->label())
            ->greeting('Hello ' . ($notifiable->firstname ?? 'there') . ',')
            ->line('Your account has been deactivated by an administrator.')
            ->line('If you believe this is a mistake, please contact support.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $type = NotificationTypeEnum::ACCOUNT_DEACTIVATED;

        return [
            'type' => $type->value,
            'title' => $type->label(),
            'message' => 'Your account has been deactivated by an administrator.',
            'severity' => $type->severity(),
            'action_url' => null,
            'meta' => [
                'deactivated_at' => now()->toIso8601String(),
            ],
        ];
    }
}
