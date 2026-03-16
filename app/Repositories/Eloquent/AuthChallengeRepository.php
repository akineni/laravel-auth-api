<?php

namespace App\Repositories\Eloquent;

use App\Models\AuthChallenge;
use App\Models\User;
use App\Repositories\Contracts\AuthChallengeRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class AuthChallengeRepository implements AuthChallengeRepositoryInterface
{
    public function createChallenge(
        User $user,
        ?string $code,
        CarbonInterface $expiresAt,
        string $method,
        string $context
    ): AuthChallenge {
        $this->clear($user, $context);

        return AuthChallenge::create([
            'user_id' => $user->id,
            'challenge_token' => Str::random(64),
            'code' => $code ? hash('sha256', $code) : null,
            'method' => $method,
            'context' => $context,
            'expires_at' => $expiresAt,
        ]);
    }

    public function findActiveByChallengeToken(string $challengeToken): ?AuthChallenge
    {
        return AuthChallenge::query()
            ->where('challenge_token', $challengeToken)
            ->whereNull('verified_at')
            ->with('user')
            ->first();
    }

    public function isChallengeExpired(AuthChallenge $challenge): bool
    {
        return now()->gt($challenge->expires_at);
    }

    public function challengeMatches(AuthChallenge $record, string $code): bool
    {
        if (!$record->code) {
            return false;
        }

        return hash_equals($record->code, hash('sha256', $code));
    }

    public function markChallengeVerified(AuthChallenge $record): bool
    {
        return $record->update([
            'verified_at' => now(),
        ]);
    }

    public function clear(User $user, ?string $context = null): bool
    {
        $query = AuthChallenge::query()
            ->where('user_id', $user->id)
            ->whereNull('verified_at');

        if ($context) {
            $query->where('context', $context);
        }

        return $query->delete() >= 0;
    }

    public function incrementAttempts(AuthChallenge $challenge): bool
    {
        return $challenge->increment('attempts') > 0;
    }

    public function hasTooManyAttempts(AuthChallenge $challenge): bool
    {
        $maxAttempts = (int) config('otp.max_verification_attempts', 5);

        return $challenge->attempts >= $maxAttempts;
    }
}