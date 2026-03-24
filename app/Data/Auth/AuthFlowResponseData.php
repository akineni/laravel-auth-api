<?php

namespace App\Data\Auth;

final readonly class AuthFlowResponseData
{
    public function __construct(
        public string $message,
        public array|OtpChallengeData|null $data = null
    ) {}

    public static function authenticated(array $authData): self
    {
        return new self(
            message: 'Login successful.',
            data: $authData,
        );
    }

    public static function otpRequired(
        OtpChallengeData $otpChallenge,
        string $message = 'Verification code required.'
    ): self {
        return new self(
            message: $message,
            data: $otpChallenge,
        );
    }

    public static function emailVerified(): self
    {
        return new self(
            message: 'Email verified successfully.',
            data: null,
        );
    }

    public static function phoneVerified(): self
    {
        return new self(
            message: 'Phone number verified successfully.',
            data: null,
        );
    }

    public static function passwordResetAuthorized(string $token, string $email): self
    {
        return new self(
            message: 'OTP verified successfully. You may now reset your password.',
            data: [
                'token' => $token,
                'email' => $email,
            ],
        );
    }

    public static function passwordResetLinkSent(): self
    {
        return new self(
            message: 'If an account with that email exists, a password reset link has been sent.',
            data: null,
        );
    }
}