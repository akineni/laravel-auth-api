<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfileUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $type = NotificationTypeEnum::PROFILE_UPDATED;

        return [
            'type' => $type->value,
            'title' => $type->label(),
            'message' => 'Your profile information was updated.',
            'severity' => $type->severity(),
            'action_url' => null,
            'meta' => [
                'updated_at' => now()->toIso8601String(),
            ],
        ];
    }
}
