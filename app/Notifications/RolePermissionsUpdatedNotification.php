<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RolePermissionsUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $roleName
    ) {}

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
        $type = NotificationTypeEnum::ROLE_PERMISSIONS_UPDATED;

        return (new MailMessage)
            ->subject($type->label())
            ->greeting('Hello ' . ($notifiable->firstname ?? 'there') . ',')
            ->line("The permissions for your '{$this->roleName}' role were updated.")
            ->line('Your access to some features may have changed.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $type = NotificationTypeEnum::ROLE_PERMISSIONS_UPDATED;

        return [
            'type' => $type->value,
            'title' => $type->label(),
            'message' => "The permissions for your '{$this->roleName}' role were updated.",
            'severity' => $type->severity(),
            'action_url' => null,
            'meta' => [
                'role_name' => $this->roleName,
                'updated_at' => now()->toIso8601String(),
            ],
        ];
    }
}
