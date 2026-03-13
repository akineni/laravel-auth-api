<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Http\Requests\SearchFilterRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\RoleResource;
use App\Helpers\ApiResponse;
use App\Models\Role;
use App\Services\RoleService;

class RoleController extends Controller
{
    public function __construct(
        protected readonly RoleService $roleService
    ) {}

    /**
     * List Roles
     *
     * @group Roles
     * @authenticated
     */
    public function index(SearchFilterRequest $request)
    {
        $roles = $this->roleService->getAll($request->validated());

        return ApiResponse::success(
            'Roles retrieved successfully',
            ApiCollection::for($roles, RoleResource::class)
        );
    }

    /**
     * Create Role
     *
     * @group Roles
     * @authenticated
     */
    public function store(RoleRequest $request)
    {
        $role = $this->roleService->create($request->validated());

        return ApiResponse::success(
            'Role created successfully',
            new RoleResource($role)
        );
    }

    /**
     * Show Role
     *
     * @group Roles
     * @authenticated
     */
    public function show(Role $role)
    {
        return ApiResponse::success(
            'Role retrieved successfully',
            new RoleResource($role)
        );
    }

    /**
     * Update Role
     *
     * @group Roles
     * @authenticated
     */
    public function update(RoleRequest $request, Role $role)
    {
        $updatedRole = $this->roleService->update($role, $request->validated());

        return ApiResponse::success(
            'Role updated successfully',
            new RoleResource($updatedRole)
        );
    }

    /**
     * Delete Role
     *
     * @group Roles
     * @authenticated
     */
    public function destroy(Role $role)
    {
        $this->roleService->destroy($role);

        return ApiResponse::success('Role deleted successfully');
    }

    /**
     * List Permissions
     *
     * @group Roles
     * @authenticated
     */
    public function getPermissions(SearchFilterRequest $request)
    {
        $permissions = $this->roleService->getAllPermissions($request->validated());

        return ApiResponse::success(
            'Permissions retrieved successfully',
            $permissions
        );
    }
}