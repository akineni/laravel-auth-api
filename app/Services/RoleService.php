<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class RoleService
{
    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->roleRepository->paginateRoles($filters);
    }

    public function create(array $data): Role
    {
        return $this->roleRepository->create($data, $this->getCurrentGuard());
    }

    public function update(Role $role, array $data): Role
    {
        return $this->roleRepository->update($role, $data);
    }

    public function destroy(Role $role): void
    {
        $this->roleRepository->delete($role);
    }

    public function getAllPermissions(array $filters = []): LengthAwarePaginator
    {
        return $this->roleRepository->paginatePermissions($filters);
    }

    private function getCurrentGuard(): string
    {
        return Auth::getDefaultDriver();
    }
}