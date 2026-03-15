<?php

namespace App\Data\Auth;

final readonly class OtpChallengeData
{
    public function __construct(
        public bool $otpRequired,
        public ?string $destination,
        public string $challengeToken,
        public int $expiresIn,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'otp_required' => $this->otpRequired,
            'destination' => $this->destination,
            'challenge_token' => $this->challengeToken,
            'expires_in' => $this->expiresIn,
        ], fn ($value) => $value !== null);
    }
}
