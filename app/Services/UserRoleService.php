<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Models\User;
use App\Repositories\Contracts\UserRoleRepositoryInterface;

class UserRoleService
{
    public function __construct(
        private readonly UserRoleRepositoryInterface $userRoleRepository
    ) {}

    public function assignRole(User $user, string $roleId): User
    {
        $role = $this->userRoleRepository->findRoleByIdOrFail($roleId);

        if ($this->userRoleRepository->userHasRole($user, $role->name)) {
            throw new ConflictException('User already has this role');
        }

        return $this->userRoleRepository->assignRole($user, $role);
    }

    public function revokeRole(User $user, string $roleId): User
    {
        $role = $this->userRoleRepository->findRoleByIdOrFail($roleId);

        if (! $this->userRoleRepository->userHasRole($user, $role->name)) {
            throw new ConflictException('User does not have this role');
        }

        return $this->userRoleRepository->revokeRole($user, $role);
    }
}