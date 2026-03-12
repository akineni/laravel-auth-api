<?php

namespace App\Repositories\Contracts;

use App\Models\{Role, User};

interface UserRoleRepositoryInterface
{
    /**
     * Find a role by ID.
     */
    public function findRoleById(string $roleId): ?Role;

    /**
     * Check if a user has a role.
     */
    public function userHasRole(User $user, string $roleName): bool;

    /**
     * Assign a role to a user.
     */
    public function assignRole(User $user, Role $role): User;

    /**
     * Revoke a role from a user.
     */
    public function revokeRole(User $user, Role $role): User;

}