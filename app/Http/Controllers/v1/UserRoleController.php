<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Helpers\ApiResponse;
use App\Services\UserRoleService;

/**
 * @group User Roles
 *
 * APIs for assigning and revoking user roles.
 */
class UserRoleController extends Controller
{
    public function __construct(
        private readonly UserRoleService $userRoleService
    ) {}

    /**
     * Assign Role
     *
     * Assign a role to a user.
     * @authenticated
     */
    public function assignRole(UserRoleRequest $request, User $user)
    {
        try {
            $user = $this->userRoleService->assignRole($user, $request->role_id);

            return ApiResponse::success(
                'Role assigned successfully',
                new UserResource($user)
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to assign role.',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Revoke Role
     *
     * Remove a role from a user.
     * @authenticated
     */
    public function revokeRole(UserRoleRequest $request, User $user)
    {
        try {
            $user = $this->userRoleService->revokeRole($user, $request->role_id);

            return ApiResponse::success(
                'Role revoked successfully',
                new UserResource($user)
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to revoke role.',
                500,
                $th->getMessage()
            );
        }
    }
}