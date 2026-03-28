<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use App\Enums\RoleActionEnum;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRoleModifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected User $subject,
        protected string $roleName,
        protected RoleActionEnum $action,
        protected ?User $actor = null
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
        $type = NotificationTypeEnum::USER_ROLE_MODIFIED;

        $mail = (new MailMessage)
            ->subject($type->label())
            ->greeting('Hello ' . ($notifiable->firstname ?? 'there') . ',')
            ->line($this->message())
            ->line("Affected user email: {$this->subject->email}");

        if ($this->actor) {
            $mail->line("Modified by: {$this->actor->fullname} ({$this->actor->email})");
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $type = NotificationTypeEnum::USER_ROLE_MODIFIED;

        return [
            'type' => $type->value,
            'title' => $type->label(),
            'message' => $this->message(),
            'severity' => $type->severity(),
            'action_url' => null,
            'meta' => [
                'action' => $this->action->value,
                'role_name' => $this->roleName,
                'subject_id' => $this->subject->id,
                'subject_fullname' => $this->subject->fullname,
                'subject_email' => $this->subject->email,
                'actor_id' => $this->actor?->id,
                'actor_fullname' => $this->actor?->fullname,
                'actor_email' => $this->actor?->email,
            ],
        ];
    }

    protected function message(): string
    {
        return "Role {$this->roleName} was {$this->action->verb()} {$this->subject->fullname}.";
    }
}