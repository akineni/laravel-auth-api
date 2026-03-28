<?php

namespace App\Services\User;

use App\Enums\RoleModificationContextEnum;
use App\Events\RoleModified;
use App\Exceptions\ConflictException;
use App\Models\User;
use App\Repositories\Contracts\UserRoleRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class UserRoleService
{
    public function __construct(
        protected UserRoleRepositoryInterface $userRoleRepository
    ) {}

    public function assignRole(User $user, string $roleId): User
    {
        $role = $this->userRoleRepository->findRoleByIdOrFail($roleId);

        if ($this->userRoleRepository->userHasRole($user, $role->name)) {
            throw new ConflictException('User already has this role');
        }

        $updatedUser = $this->userRoleRepository->assignRole($user, $role);

        $actor = Auth::user();

        // Dispatch role modified event (role assigned)
        RoleModified::dispatch(
            $updatedUser,
            $role,
            'assigned',
            $actor instanceof User ? $actor : null,
            RoleModificationContextEnum::MANUAL_ASSIGNMENT
        );

        return $updatedUser;
    }

    public function revokeRole(User $user, string $roleId): User
    {
        $role = $this->userRoleRepository->findRoleByIdOrFail($roleId);

        if (! $this->userRoleRepository->userHasRole($user, $role->name)) {
            throw new ConflictException('User does not have this role');
        }

        $updatedUser = $this->userRoleRepository->revokeRole($user, $role);

        $actor = Auth::user();

        // Dispatch role modified event (role revoked)
        RoleModified::dispatch(
            $updatedUser,
            $role,
            'revoked',
            $actor instanceof User ? $actor : null,
            RoleModificationContextEnum::MANUAL_REVOCATION
        );

        return $updatedUser;
    }
}