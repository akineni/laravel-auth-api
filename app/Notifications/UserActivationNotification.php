<?php

namespace App\Notifications;

use Firebase\JWT\JWT;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class UserActivationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail message.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $token = $this->generateActivationToken($notifiable);
        $activationUrl = $this->buildActivationUrl($token);
        $ttl = (int) config('tokens.activation.ttl_hours', 2);

        return (new MailMessage)
            ->subject('Activate Your Account')
            ->greeting("Hello {$notifiable->firstname},")
            ->line('An account has been created for you by the administrator.')
            ->line('To start using it, please activate your account by clicking the button below.')
            ->action('Activate Account', $activationUrl)
            ->line("This link will expire in {$ttl} hour(s) for security reasons.")
            ->line('If you were not expecting this, you can safely ignore this email.');
    }

    /**
     * Generate activation token.
     */
    private function generateActivationToken(object $notifiable): string
    {
        $secret = config('tokens.activation.secret');
        $ttl = (int) config('tokens.activation.ttl_hours', 2);

        if (! is_string($secret) || trim($secret) === '') {
            throw new \RuntimeException('Activation token secret is not configured.');
        }

        $now = now();

        $payload = [
            'sub' => $notifiable->id,
            'purpose' => 'account_activation',
            'iat' => $now->timestamp,
            'exp' => $now->copy()->addHours($ttl)->timestamp,
            'jti' => (string) Str::uuid(),
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    /**
     * Build activation URL.
     */
    private function buildActivationUrl(string $token): string
    {
        $baseUrl = config('frontend.email_verification_url');

        if (! is_string($baseUrl) || trim($baseUrl) === '') {
            throw new \RuntimeException('Frontend email verification URL is not configured.');
        }

        return rtrim($baseUrl, '/') . '?' . http_build_query([
            'token' => $token,
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}