<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\UserRoleRepositoryInterface;
use Illuminate\Support\Facades\DB;

class UserRoleRepository implements UserRoleRepositoryInterface
{
    /**
     * Find a role by ID or fail.
     */
    public function findRoleByIdOrFail(string $roleId): Role
    {
        return Role::query()
            ->with('permissions')
            ->findOrFail($roleId);
    }

    /**
     * Check if a user has a role.
     */
    public function userHasRole(User $user, string $roleName): bool
    {
        return $user->hasRole($roleName);
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(User $user, Role $role): User
    {
        DB::transaction(function () use ($user, $role) {
            $user->assignRole($role);
        });

        return $user->fresh()->load('roles');
    }

    /**
     * Revoke a role from a user.
     */
    public function revokeRole(User $user, Role $role): User
    {
        DB::transaction(function () use ($user, $role) {
            $user->removeRole($role);
        });

        return $user->fresh()->load('roles');
    }
}