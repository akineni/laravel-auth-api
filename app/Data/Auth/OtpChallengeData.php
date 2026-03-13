<?php

namespace App\Data\Auth;

final readonly class OtpChallengeData
{
    public function __construct(
        public bool $otpRequired,
        public string $destination,
        public string $challengeToken,
        public int $expiresIn
    ) {}
}