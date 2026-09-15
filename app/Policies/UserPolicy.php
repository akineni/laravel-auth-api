<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the actor can delete the target user.
     *
     * Rules, in order:
     *  1. Nobody can delete their own account through this endpoint.
     *  2. An actor cannot delete a user with a higher-privileged role than
     *     their own (e.g. an Admin cannot delete a Super Admin).
     *  3. The only remaining Super Admin can never be deleted, by anyone,
     *     so the platform can never be left without an administrator able
     *     to manage it.
     */
    public function delete(User $actor, User $target): Response
    {
        if ($actor->is($target)) {
            return Response::deny('You cannot delete your own account.');
        }

        if ($this->rankOf($target) > $this->rankOf($actor)) {
            return Response::deny('You cannot delete a user with a higher-privileged role than yours.');
        }

        if ($target->hasRole(RoleEnum::SUPER_ADMIN->value) && $this->isOnlyRemainingSuperAdmin()) {
            return Response::deny('You cannot delete the only remaining Super Admin.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the actor can deactivate the target user.
     *
     * Mirrors delete()'s rules, since deactivation immediately cuts the
     * target off (their active session is revoked) just like deletion does:
     *  1. Nobody can deactivate their own account through this endpoint.
     *  2. An actor cannot deactivate a user with a higher-privileged role
     *     than their own (e.g. an Admin cannot deactivate a Super Admin).
     *  3. The only remaining *active* Super Admin can never be deactivated,
     *     by anyone, so the platform can never be left without an
     *     administrator able to log in and manage it.
     */
    public function deactivate(User $actor, User $target): Response
    {
        if ($actor->is($target)) {
            return Response::deny('You cannot deactivate your own account.');
        }

        if ($this->rankOf($target) > $this->rankOf($actor)) {
            return Response::deny('You cannot deactivate a user with a higher-privileged role than yours.');
        }

        if ($target->hasRole(RoleEnum::SUPER_ADMIN->value) && $this->isOnlyRemainingActiveSuperAdmin($target)) {
            return Response::deny('You cannot deactivate the only remaining active Super Admin.');
        }

        return Response::allow();
    }

    /**
     * Rank a user by their highest privileged role. Anyone without an
     * admin role ranks below both, so an Admin/Super Admin can still
     * delete ordinary users.
     */
    private function rankOf(User $user): int
    {
        return match (true) {
            $user->hasRole(RoleEnum::SUPER_ADMIN->value) => 2,
            $user->hasRole(RoleEnum::ADMIN->value) => 1,
            default => 0,
        };
    }

    private function isOnlyRemainingSuperAdmin(): bool
    {
        return User::role(RoleEnum::SUPER_ADMIN->value)->count() <= 1;
    }

    /**
     * Unlike isOnlyRemainingSuperAdmin(), this also excludes Super Admins
     * who are already inactive, since they cannot log in to manage the
     * platform even though their account still exists.
     */
    private function isOnlyRemainingActiveSuperAdmin(User $target): bool
    {
        $activeSuperAdmins = User::role(RoleEnum::SUPER_ADMIN->value)
            ->where('status', UserStatusEnum::ACTIVE->value)
            ->count();

        return $target->status === UserStatusEnum::ACTIVE->value && $activeSuperAdmins <= 1;
    }
}
