<?php

namespace App\Repositories\Contracts;

use App\Models\AuthSession;
use App\Models\User;

interface AuthSessionRepositoryInterface
{
    /**
     * Create a new auth session for a user.
     */
    public function create(User $user, array $data): AuthSession;

    /**
     * Retrieve a session by user ID and session primary key.
     */
    public function findByUserIdAndId(string $userId, string $id): ?AuthSession;

    /**
     * Update session activity timestamp (and optional metadata).
     */
    public function updateActivity(AuthSession $session, array $data = []): bool;

    /**
     * Mark a session as revoked.
     */
    public function revoke(AuthSession $session): bool;

    /**
     * Revoke a session using user ID and session primary key.
     */
    public function revokeByUserIdAndId(string $userId, string $id): bool;
}