<?php

namespace App\Notifications;

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
        protected string $action,
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
        $verb = $this->resolveVerb();

        $mail = (new MailMessage)
            ->subject('User Role Modified')
            ->greeting('Hello ' . ($notifiable->firstname ?? 'there') . ',')
            ->line("Role {$this->roleName} was {$verb} {$this->subject->fullname}.")
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
        $verb = $this->resolveVerb();

        return [
            'type' => 'user_role_modified',
            'title' => 'User role modified',
            'message' => "Role {$this->roleName} was {$verb} {$this->subject->fullname}.",
            'severity' => 'info',
            'action_url' => null,
            'meta' => [
                'action' => $this->action,
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

    protected function resolveVerb(): string
    {
        return $this->action === 'assigned'
            ? 'assigned to'
            : 'revoked from';
    }
}
