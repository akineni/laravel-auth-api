<?php

namespace App\Notifications;

use App\Enums\NotificationTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class LoginDetectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected array $context = []
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
        $type = NotificationTypeEnum::LOGIN_DETECTED;

        $mail = (new MailMessage)
            ->subject($type->label())
            ->greeting('Hello ' . ($notifiable->firstname ?? 'there') . ',')
            ->line('We detected a successful login to your account.');

        if (! empty($this->context['logged_in_at'])) {
            $formattedTime = Carbon::parse($this->context['logged_in_at'])
                ->timezone(config('app.timezone')) // or user's timezone later
                ->format('F j, Y \a\t g:i A');

            $mail->line('Time: ' . $formattedTime);
        }

        if (! empty($this->context['ip_address'])) {
            $mail->line('IP address: ' . $this->context['ip_address']);
        }

        if (! empty($this->context['device'])) {
            $mail->line('Device: ' . $this->context['device']);
        }

        if (! empty($this->context['browser'])) {
            $mail->line('Browser: ' . $this->context['browser']);
        }

        if (! empty($this->context['platform'])) {
            $mail->line('Platform: ' . $this->context['platform']);
        }

        return $mail->line('If this was not you, please change your password immediately and contact support.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $type = NotificationTypeEnum::LOGIN_DETECTED;

        return [
            'type' => $type->value,
            'title' => $type->label(),
            'message' => 'A successful login to your account was detected.',
            'severity' => $type->severity(),
            'action_url' => null,
            'meta' => [
                'logged_in_at' => $this->context['logged_in_at'] ?? now()->toIso8601String(),
                'ip_address' => $this->context['ip_address'] ?? null,
                'user_agent' => $this->context['user_agent'] ?? null,
                'device' => $this->context['device'] ?? null,
                'browser' => $this->context['browser'] ?? null,
                'platform' => $this->context['platform'] ?? null,
            ],
        ];
    }
}
