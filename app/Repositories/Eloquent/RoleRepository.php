<?php

namespace App\Repositories\Eloquent;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleRepository implements RoleRepositoryInterface
{
    /**
     * Paginate roles with optional filters.
     */
    public function paginateRoles(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? config('app.pagination_per_page');

        return Role::query()
            ->withCount('users')
            ->search($filters['search'] ?? null, ['name'])
            ->createdBetween(
                $filters['start_date'] ?? null,
                $filters['end_date'] ?? null
            )
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new role.
     */
    public function create(array $data, string $guard): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $guard,
        ]);

        if (!empty($data['permissions']) && is_array($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->fresh();
    }

    /**
     * Update an existing role.
     */
    public function update(Role $role, array $data): Role
    {
        $role->update([
            'name' => $data['name'] ?? $role->name,
        ]);

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->fresh();
    }

    /**
     * Delete a role.
     */
    public function delete(Role $role): void
    {
        $role->delete();
    }

    /**
     * Paginate permissions with optional filters.
     */
    public function paginatePermissions(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? config('app.pagination_per_page');

        return Permission::query()
            ->search($filters['search'] ?? null, ['name'])
            ->createdBetween(
                $filters['start_date'] ?? null,
                $filters['end_date'] ?? null
            )
            ->latest()
            ->paginate($perPage);
    }
}