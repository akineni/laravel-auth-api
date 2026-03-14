<?php

namespace App\Repositories\Eloquent;

use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function findById(string $id, bool $includeTrashed = false): ?User
    {
        $query = User::query();

        if ($includeTrashed) {
            $query->withTrashed();
        }

        return $query->find($id);
    }

    public function findByEmail(string $email, bool $includeTrashed = false): ?User
    {
        $query = User::query();

        if ($includeTrashed) {
            $query->withTrashed();
        }

        return $query
            ->where('email', $email)
            ->first();
    }

    public function resetFailedLoginAttempts(User $user): bool
    {
        return $user->update([
            'failed_logins' => 0,
            'locked_until' => null,
        ]);
    }

    public function incrementFailedLogins(User $user): bool
    {
        return $user->increment('failed_logins') > 0;
    }

    public function lockUntil(User $user, \DateTimeInterface $lockedUntil): bool
    {
        $user->locked_until = $lockedUntil;

        return $user->save();
    }

    public function updatePassword(User $user, string $password, bool $clearOtpVerification = true): bool
    {
        $payload = [
            'password' => $password,
        ];

        if ($clearOtpVerification) {
            $payload['otp_verified_at'] = null;
        }

        return $user->update($payload);
    }

    public function save(User $user): bool
    {
        return $user->save();
    }

    public function verifyEmailAndActivate(User $user): bool
    {
        return $user->update([
            'email_verified_at' => now(),
            'status' => UserStatusEnum::ACTIVE->value,
        ]);
    }

    public function getAll(): Collection
    {
        return User::all();
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? config('app.pagination_per_page');

        return User::query()
            ->search($filters['search'] ?? null, [
                'fullname',
                'firstname',
                'lastname',
                'email',
                'phone_number',
            ])
            ->filterStatus($filters['status'] ?? null)
            ->createdBetween(
                $filters['start_date'] ?? null,
                $filters['end_date'] ?? null
            )
            ->latest()
            ->paginate($perPage);
    }

    public function findByIdOrFail(string $id): User
    {
        return User::query()->findOrFail($id);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    public function restore(User $user): bool
    {
        return (bool) $user->restore();
    }

    public function assignRole(User $user, string|array $roles): void
    {
        $user->assignRole($roles);
    }

    public function syncRoles(User $user, array $roles): void
    {
        $user->syncRoles($roles);
    }

    public function loadMissing(User $user, string|array $relations): User
    {
        $user->loadMissing($relations);

        return $user;
    }
}