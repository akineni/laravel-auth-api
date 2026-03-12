<?php

namespace App\Enums;

enum OtpContextEnum: string
{
    case LOGIN = 'login';
    case PASSWORD_RESET = 'password_reset';
    case EMAIL_VERIFICATION = 'email_verification';
    case PHONE_VERIFICATION = 'phone_verification';

    /**
     * Get all enum values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Determine if a value is a valid OTP context.
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    public function message(): string
    {
        return match ($this) {
            self::LOGIN => 'You requested a login verification code.',
            self::PASSWORD_RESET => 'You requested a password reset verification code.',
            self::EMAIL_VERIFICATION => 'You requested to verify your email address.',
            self::PHONE_VERIFICATION => 'You requested to verify your phone number.',
        };
    }
}