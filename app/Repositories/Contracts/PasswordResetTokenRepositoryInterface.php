<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface PasswordResetTokenRepositoryInterface
{
    /**
     * Create a password reset token for the given user.
     *
     * Returns the newly generated reset token.
     */
    public function create(User $user): string;

    /**
     * Determine whether the given reset token is valid for the user.
     *
     * Returns true when the token exists and is still valid.
     */
    public function exists(User $user, string $token): bool;

    /**
     * Delete the password reset token for the given user.
     *
     * Returns true when the token is successfully removed.
     */
    public function delete(User $user): bool;
}