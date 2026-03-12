<?php

namespace App\Repositories\Contracts;

use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

interface RoleRepositoryInterface
{
    /**
     * Paginate roles with optional filters.
     */
    public function paginateRoles(array $filters = []): LengthAwarePaginator;

    /**
     * Create a new role.
     */
    public function create(array $data, string $guard): Role;

    /**
     * Update an existing role.
     */
    public function update(Role $role, array $data): Role;

    /**
     * Delete a role.
     */
    public function delete(Role $role): void;

    /**
     * Paginate permissions with optional filters.
     */
    public function paginatePermissions(array $filters = []): LengthAwarePaginator;
}