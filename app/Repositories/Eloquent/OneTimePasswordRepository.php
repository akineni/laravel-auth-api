<?php

namespace App\Repositories\Eloquent;

use App\Models\OneTimePassword;
use App\Models\User;
use App\Repositories\Contracts\OneTimePasswordRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class OneTimePasswordRepository implements OneTimePasswordRepositoryInterface
{
    public function createChallenge(
        User $user,
        string $code,
        CarbonInterface $expiresAt,
        ?string $channel = null,
        ?string $context = null
    ): OneTimePassword {
        $this->clear($user, $context);

        return OneTimePassword::create([
            'user_id' => $user->id,
            'challenge_token' => Str::random(64),
            'code' => hash('sha256', $code),
            'channel' => $channel,
            'context' => $context,
            'expires_at' => $expiresAt,
        ]);
    }

    public function findActiveByChallengeToken(string $challengeToken): ?OneTimePassword
    {
        return OneTimePassword::query()
            ->where('challenge_token', $challengeToken)
            ->whereNull('verified_at')
            ->with('user')
            ->first();
    }

    public function isChallengeExpired(OneTimePassword $otp): bool
    {
        return now()->gt($otp->expires_at);
    }

    public function challengeMatches(OneTimePassword $record, string $otp): bool
    {
        return hash_equals($record->code, hash('sha256', $otp));
    }

    public function markChallengeVerified(OneTimePassword $record): bool
    {
        return $record->update([
            'verified_at' => now(),
        ]);
    }

    public function clear(User $user, ?string $context = null): bool
    {
        $query = OneTimePassword::query()
            ->where('user_id', $user->id)
            ->whereNull('verified_at');

        if ($context) {
            $query->where('context', $context);
        }

        return $query->delete() >= 0;
    }
}