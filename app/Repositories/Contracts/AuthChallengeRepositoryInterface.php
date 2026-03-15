<?php

namespace App\Repositories\Contracts;

use App\Models\AuthChallenge;
use App\Models\User;
use Carbon\CarbonInterface;

interface AuthChallengeRepositoryInterface
{
    /**
     * Create a new auth challenge and return the created record.
     */
    public function createChallenge(
        User $user,
        ?string $code,
        CarbonInterface $expiresAt,
        string $method,
        string $context
    ): AuthChallenge;

    /**
     * Find an active auth challenge by challenge token.
     */
    public function findActiveByChallengeToken(string $challengeToken): ?AuthChallenge;

    /**
     * Determine whether a challenge is expired.
     */
    public function isChallengeExpired(AuthChallenge $challenge): bool;

    /**
     * Check if the provided code matches the stored challenge code.
     */
    public function challengeMatches(AuthChallenge $record, string $code): bool;

    /**
     * Mark a challenge as verified.
     */
    public function markChallengeVerified(AuthChallenge $record): bool;

    /**
     * Clear active challenges for a user and optional context.
     */
    public function clear(User $user, ?string $context = null): bool;
}