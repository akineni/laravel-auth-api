<?php

namespace App\Services;

use App\Models\Role;
use App\Notifications\RoleDeletedNotification;
use App\Notifications\RolePermissionsUpdatedNotification;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\Notification\NotificationFanOutService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class RoleService
{
    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository,
        private readonly NotificationFanOutService $fanOutService
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
        $updatedRole = $this->roleRepository->update($role, $data);

        if (array_key_exists('permissions', $data)) {
            $this->fanOutService->notifyMany(
                $updatedRole->users,
                new RolePermissionsUpdatedNotification($updatedRole->name)
            );
        }

        return $updatedRole;
    }

    public function destroy(Role $role): void
    {
        $affectedUsers = $role->users()->get();
        $roleName = $role->name;

        $this->roleRepository->delete($role);

        $this->fanOutService->notifyMany(
            $affectedUsers,
            new RoleDeletedNotification($roleName)
        );
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