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

    public static function otpRequired(OtpChallengeData $otpChallenge): self
    {
        return new self(
            message: 'OTP sent to your email',
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

    public static function passwordResetVerified(): self
    {
        return new self(
            message: 'OTP verified successfully. You may now reset your password.',
            data: null,
        );
    }
}