<?php

namespace App\Repositories\Contracts;

use App\Models\OneTimePassword;
use App\Models\User;
use Carbon\CarbonInterface;

interface OneTimePasswordRepositoryInterface
{
    /**
     * Create a new OTP challenge and return the created record.
     */
    public function createChallenge(
        User $user,
        string $code,
        CarbonInterface $expiresAt,
        ?string $channel = null,
        ?string $context = null
    ): OneTimePassword;

    /**
     * Find an active OTP challenge by challenge token.
     */
    public function findActiveByChallengeToken(string $challengeToken): ?OneTimePassword;

    /**
     * Determine whether a challenge is expired.
     */
    public function isChallengeExpired(OneTimePassword $otp): bool;

    /**
     * Check if the provided OTP matches the stored challenge code.
     */
    public function challengeMatches(OneTimePassword $record, string $otp): bool;

    /**
     * Mark a challenge as verified.
     */
    public function markChallengeVerified(OneTimePassword $record): bool;

    /**
     * Clear active challenges for a user and optional context.
     */
    public function clear(User $user, ?string $context = null): bool;
}