<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Create a new user record.
     */
    public function create(array $data): User;

    /**
     * Find a user by their primary ID.
     *
     * @param string $id
     * @param bool $includeTrashed Include soft deleted users
     */
    public function findById(string $id, bool $includeTrashed = false): ?User;

    /**
     * Find a user by email.
     *
     * @param string $email
     * @param bool $withTrashed Include soft deleted users
     */
    public function findByEmail(string $email, bool $includeTrashed = false): ?User;

    /**
     * Find a user by username.
     *
     * @param string $username
     * @param bool $includeTrashed Include soft deleted users
     */
    public function findByUsername(string $username, bool $includeTrashed = false): ?User;

    /**
     * Reset failed login attempts.
     */
    public function resetFailedLoginAttempts(User $user): bool;

    /**
     * Increment failed login attempts.
     */
    public function incrementFailedLogins(User $user): bool;

    /**
     * Lock user account until a given time.
     */
    public function lockUntil(User $user, \DateTimeInterface $lockedUntil): bool;

    /**
     * Update user password.
     */
    public function updatePassword(User $user, string $password, bool $clearOtpVerification = true): bool;

    /**
     * Save a user model.
     */
    public function save(User $user): bool;

    /**
     * Verify email and activate account.
     */
    public function verifyEmailAndActivate(User $user): bool;

    /**
     * Get all users.
     */
    public function getAll(): Collection;

    /**
     * Get paginated users.
     */
    public function paginate(array $filters = []): LengthAwarePaginator;

    /**
     * Paginate admin users (Admin and Super Admin roles).
     */
    public function paginateAdmins(array $filters = []): LengthAwarePaginator;

    /**
     * Find a user by ID or fail.
     */
    public function findByIdOrFail(string $id): User;

    /**
     * Update a user record.
     */
    public function update(User $user, array $data): bool;

    /**
     * Delete a user.
     */
    public function delete(User $user): bool;

    /**
     * Restore a soft deleted user.
     */
    public function restore(User $user): bool;

    /**
     * Assign roles to a user.
     */
    public function assignRole(User $user, string|array $roles): void;

    /**
     * Sync user roles.
     */
    public function syncRoles(User $user, array $roles): void;

    /**
     * Load missing user relationships.
     */
    public function loadMissing(User $user, string|array $relations): User;
}