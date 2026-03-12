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
        try {
            $roles = $this->roleService->getAll($request->validated());

            return ApiResponse::success(
                'Roles retrieved successfully',
                ApiCollection::for($roles, RoleResource::class)
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to retrieve roles.',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Create Role
     *
     * @group Roles
     * @authenticated
     */
    public function store(RoleRequest $request)
    {
        try {
            $data = $this->roleService->create($request->validated());

            return ApiResponse::success(
                'Role created successfully',
                new RoleResource($data)
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to create role.',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Show Role
     *
     * @group Roles
     * @authenticated
     */
    public function show(Role $role)
    {
        try {
            return ApiResponse::success(
                'Role retrieved successfully',
                new RoleResource($role)
            );

        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to retrieve role.',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Update Role
     *
     * @group Roles
     * @authenticated
     */
    public function update(RoleRequest $request, Role $role)
    {
        try {
            $updatedRole = $this->roleService->update($role, $request->validated());

            return ApiResponse::success(
                'Role updated successfully',
                new RoleResource($updatedRole)
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to update role.',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Delete Role
     *
     * @group Roles
     * @authenticated
     */
    public function destroy(Role $role)
    {
        try {
            $this->roleService->destroy($role);

            return ApiResponse::success('Role deleted successfully');
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to delete role.',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * List Permissions
     *
     * @group Roles
     * @authenticated
     */
    public function getPermissions(SearchFilterRequest $request)
    {
        try {
            $permissions = $this->roleService->getAllPermissions($request->validated());

            return ApiResponse::success(
                'Permissions retrieved successfully',
                $permissions
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to retrieve permissions.',
                500,
                $th->getMessage()
            );
        }
    }
}