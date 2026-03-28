<?php

namespace App\Http\Controllers\v1\User;

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
use App\Services\User\UserService;

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
        $user = $this->userService->getCurrentUser();

        return ApiResponse::success(
            'Current user retrieved successfully',
            new UserResource($user)
        );
    }

    /**
     * Create User
     *
     * Create a new user.
     * @authenticated
     */
    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        return ApiResponse::success(
            'User created successfully. Activation email sent.',
            new UserResource($user),
            201
        );
    }

    /**
     * List Users
     *
     * Retrieve all users.
     * @authenticated
     */
    public function index(SearchFilterRequest $request)
    {
        $users = $this->userService->getAllUsersPaginated($request->validated());

        return ApiResponse::success(
            'Users retrieved successfully',
            ApiCollection::for($users, UserMiniResource::class)
        );
    }

    /**
     * Show User
     *
     * Retrieve a user's details.
     * @authenticated
     */
    public function show(User $user)
    {
        $user->loadMissing('roles');

        return ApiResponse::success(
            'User retrieved successfully',
            new UserResource($user)
        );
    }

    /**
     * Update User
     *
     * Update an existing user.
     * @authenticated
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $updatedUser = $this->userService->update($user, $request->validated());

        return ApiResponse::success(
            'User updated successfully',
            new UserResource($updatedUser)
        );
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
        $user = $this->userService->update($request->user(), $request->validated());

        return ApiResponse::success(
            'Profile updated successfully',
            new UserResource($user)
        );
    }

    /**
     * Delete User
     *
     * Delete a user.
     * @authenticated
     */
    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);

        return ApiResponse::success('User deleted successfully');
    }

    /**
     * Activate User
     *
     * Activate a user
     * @authenticated
     */
    public function activateUser(User $user)
    {
        $updatedUser = $this->userService->activateUser($user);

        return ApiResponse::success(
            'User activated successfully',
            new UserResource($updatedUser)
        );
    }

    /**
     * Deactivate User
     *
     * Deactivate a user
     * @authenticated
     */
    public function deactivateUser(User $user)
    {
        $updatedUser = $this->userService->deactivateUser($user);

        return ApiResponse::success(
            'User deactivated successfully',
            new UserResource($updatedUser)
        );
    }

    /**
     * Activate Account
     *
     * Activate a user account with token and password.
     * @authenticated
     */
    public function activateUserAccount(ActivateUserAccountRequest $request)
    {
        $validated = $request->validated();

        $user = $this->userService->activateAccount(
            $validated['token'],
            $validated['password']
        );

        return ApiResponse::success(
            'User activated successfully',
            new UserResource($user)
        );
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
        $this->userService->changePassword(
            $request->user(),
            $request->validated('new_password')
        );

        return ApiResponse::success('Password changed successfully');
    }

    /**
     * List Admin Users
     *
     * Retrieve all users with Admin or Super Admin roles.
     *
     * @authenticated
     */
    public function admins(SearchFilterRequest $request)
    {
        $admins = $this->userService->getAdminUsersPaginated($request->validated());

        return ApiResponse::success(
            'Admin users retrieved successfully',
            ApiCollection::for($admins, UserMiniResource::class)
        );
    }
}