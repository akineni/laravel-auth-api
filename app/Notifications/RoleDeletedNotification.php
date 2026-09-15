<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RoleDeletedNotification extends Notification implements ShouldQueue
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
        $type = NotificationTypeEnum::ROLE_DELETED;

        return (new MailMessage)
            ->subject($type->label())
            ->greeting('Hello ' . ($notifiable->firstname ?? 'there') . ',')
            ->line("The '{$this->roleName}' role was deleted.")
            ->line('Any permissions it granted you have been removed.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $type = NotificationTypeEnum::ROLE_DELETED;

        return [
            'type' => $type->value,
            'title' => $type->label(),
            'message' => "The '{$this->roleName}' role was deleted.",
            'severity' => $type->severity(),
            'action_url' => null,
            'meta' => [
                'role_name' => $this->roleName,
                'deleted_at' => now()->toIso8601String(),
            ],
        ];
    }
}
