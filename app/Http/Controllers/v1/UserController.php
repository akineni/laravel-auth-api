<?php

namespace App\Http\Controllers\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\{
    ActivateUserAccountRequest,
    ChangePasswordRequest,
    SearchFilterRequest,
    StoreUserRequest,
    UpdateProfileRequest,
    UpdateUserRequest
};
use App\Http\Resources\{ApiCollection, UserMiniResource, UserResource};
use App\Models\User;
use App\Services\UserService;

/**
 * @group Users
 *
 * APIs for managing users and account access.
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

   /**
     * Current User
     *
     * Get the currently authenticated user.
     *
     * @subgroup Current User
     * @subgroupDescription Endpoints for viewing and managing the authenticated user.
     * @authenticated
     */
    public function currentUser()
    {
        try {
            $user = $this->userService->getCurrentUser();

            return ApiResponse::success(
                'Current user retrieved successfully',
                new UserResource($user)
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to get current user',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Create User
     *
     * Create a new user.
     * @authenticated
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return ApiResponse::success(
                'User created successfully. Activation email sent.',
                new UserResource($user),
                201
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to create user',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * List Users
     *
     * Retrieve all users.
     * @authenticated
     */
    public function index(SearchFilterRequest $request)
    {
        try {
            $users = $this->userService->getAllUsersPaginated($request->validated());

            return ApiResponse::success(
                'Users retrieved successfully',
                ApiCollection::for($users, UserMiniResource::class)
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to fetch users',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Show User
     *
     * Retrieve a user's details.
     * @authenticated
     */
    public function show(User $user)
    {
        try {
            $user->loadMissing('roles');

            return ApiResponse::success(
                'User retrieved successfully',
                new UserResource($user)
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to fetch user details',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Update User
     *
     * Update an existing user.
     * @authenticated
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $updatedUser = $this->userService->update($user, $request->validated());

            return ApiResponse::success(
                'User updated successfully',
                new UserResource($updatedUser)
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to update user',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Update Current User
     *
     * Update the currently authenticated user.
     *
     * @subgroup Current User
     * @authenticated
     */
    public function updateMe(UpdateProfileRequest $request)
    {
        try {
            $user = $this->userService->update($request->user(), $request->validated());

            return ApiResponse::success(
                'Profile updated successfully',
                new UserResource($user)
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to update profile.',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Delete User
     *
     * Delete a user.
     * @authenticated
     */
    public function destroy(User $user)
    {
        try {
            $this->userService->deleteUser($user);

            return ApiResponse::success('User deleted successfully');
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to delete user',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Activate User
     *
     * Activate a user
     * @authenticated
     */
    public function activateUser(User $user)
    {
        try {
            $updatedUser = $this->userService->activateUser($user);

            return ApiResponse::success(
                'User activated successfully',
                new UserResource($updatedUser)
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to activate user',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Deactivate User
     *
     * Deactivate a user
     * @authenticated
     */
    public function deactivateUser(User $user)
    {
        try {
            $updatedUser = $this->userService->deactivateUser($user);

            return ApiResponse::success(
                'User deactivated successfully',
                new UserResource($updatedUser)
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to deactivate user',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Activate Account
     *
     * Activate a user account with token and password.
     * @authenticated
     */
    public function activateUserAccount(ActivateUserAccountRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = $this->userService->activateAccount(
                $validated['token'],
                $validated['password']
            );

            return ApiResponse::success(
                'User activated successfully',
                new UserResource($user)
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();

            return ApiResponse::error(
                'Activation failed: ' . $firstError,
                $e->status,
                $e->errors()
            );
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to activate account',
                500,
                $th->getMessage()
            );
        }
    }

    /**
     * Change My Password
     *
     * Change the authenticated user's password.
     *
     * @subgroup Current User
     * @authenticated
     */
    public function changeMyPassword(ChangePasswordRequest $request)
    {
        try {
            $this->userService->changePassword(
                $request->user(),
                $request->validated()['new_password']
            );

            return ApiResponse::success('Password changed successfully');
        } catch (\Throwable $th) {
            return ApiResponse::error(
                'Failed to change password.',
                500,
                $th->getMessage()
            );
        }
    }
}