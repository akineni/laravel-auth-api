<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RoleRevokedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('Role Revoked')
            ->greeting('Hello ' . ($notifiable->firstname ?? 'there') . ',')
            ->line("A role ({$this->roleName}) has been removed from your account.")
            ->line('If you were not expecting this change, please contact support.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'role_revoked',
            'title' => 'Role revoked',
            'message' => "A role ({$this->roleName}) has been removed from your account.",
            'severity' => 'warning',
            'action_url' => null,
            'meta' => [
                'role_name' => $this->roleName,
            ],
        ];
    }
}
