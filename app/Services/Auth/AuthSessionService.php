<?php

namespace App\Services\Auth;

use App\Exceptions\Auth\{InvalidSessionException, SessionExpiredException};
use App\Models\{AuthSession, User};
use App\Repositories\Contracts\AuthSessionRepositoryInterface;
use Illuminate\Http\Request;

class AuthSessionService
{
    public function __construct(
        private readonly AuthSessionRepositoryInterface $authSessionRepository,
    ) {}

    public function create(User $user, ?Request $request = null): AuthSession
    {
        return $this->authSessionRepository->create($user, [
            'last_activity_at' => now(),
            'ip_address' => $request?->ip(),
            'user_agent' => (string) $request?->userAgent(),
        ]);
    }

    public function revokeExistingSessions(User $user): int
    {
        return $this->authSessionRepository->revokeActiveByUserId(
            (string) $user->getKey()
        );
    }

    public function validate(string $userId, string $sessionId): AuthSession
    {
        $session = $this->authSessionRepository->findByUserIdAndId($userId, $sessionId);

        if (!$session) {
            throw new InvalidSessionException('Invalid session. Please log in again.');
        }

        if ($session->isRevoked()) {
            throw new InvalidSessionException('Session is no longer valid. Please log in again.');
        }

        $timeoutMinutes = (int) config('auth.session_idle_timeout_minutes', 30);
        $lastActivity = $session->last_activity_at ?? $session->created_at;

        if ($lastActivity->copy()->addMinutes($timeoutMinutes)->isPast()) {
            $this->authSessionRepository->revoke($session);

            throw new SessionExpiredException('Your session has expired due to inactivity. Please log in again.');
        }

        return $session;
    }

    public function touch(AuthSession $session, ?Request $request = null): void
    {
        if (
            $session->last_activity_at &&
            $session->last_activity_at->diffInSeconds(now()) < 60
        ) {
            return;
        }

        $this->authSessionRepository->updateActivity($session, [
            'ip_address' => $request?->ip(),
            'user_agent' => (string) $request?->userAgent(),
        ]);
    }

    public function revokeByUserAndSessionId(string $userId, string $sessionId): bool
    {
        return $this->authSessionRepository->revokeByUserIdAndId($userId, $sessionId);
    }
}