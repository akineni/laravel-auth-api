<?php

namespace App\Repositories\Eloquent;

use App\Models\AuthSession;
use App\Models\User;
use App\Repositories\Contracts\AuthSessionRepositoryInterface;
use Illuminate\Support\Collection;

class AuthSessionRepository implements AuthSessionRepositoryInterface
{
    public function __construct(
        private readonly AuthSession $model
    ) {}

    public function create(User $user, array $data): AuthSession
    {
        /** @var AuthSession $session */
        $session = $user->authSessions()->create($data);

        return $session;
    }

    public function findByUserIdAndId(string $userId, string $id): ?AuthSession
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('id', $id)
            ->first();
    }

    public function findActiveByUserId(string $userId): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->get();
    }

    public function updateActivity(AuthSession $session, array $data = []): bool
    {
        return $session->forceFill([
            'last_activity_at' => now(),
            ...$data,
        ])->save();
    }

    public function revoke(AuthSession $session): bool
    {
        return $session->forceFill([
            'revoked_at' => now(),
        ])->save();
    }

    public function revokeActiveByUserId(string $userId): int
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function revokeByUserIdAndId(string $userId, string $id): bool
    {
        $session = $this->findByUserIdAndId($userId, $id);

        if (!$session) {
            return false;
        }

        return $this->revoke($session);
    }
}