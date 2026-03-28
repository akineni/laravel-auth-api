<?php

namespace App\Services\User;

use App\Enums\{RoleActionEnum, RoleModificationContextEnum, SignupSourceEnum, UserStatusEnum};
use App\Events\RoleModified;
use App\Exceptions\ConflictException;
use App\Helpers\FileUploadHelper;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\UserActivationNotification;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Auth\AuthService;
use Firebase\JWT\{JWT, Key};
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly AuthService $authService,
    ) {}

    /**
     * Get currently authenticated user.
     */
    public function getCurrentUser(): User
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return $user;
    }

    /**
     * Get paginated users.
     */
    public function getAllUsersPaginated(array $filters = []): LengthAwarePaginator
    {
        return $this->userRepository->paginate($filters);
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        $roleId = $data['assigned_role_id'];

        $userData = collect($data)
            ->except(['assigned_role_id'])
            ->toArray();

        $user = DB::transaction(function () use ($userData, $roleId) {
            $user = $this->storeUser($userData);
            $this->userRepository->assignRole($user, $roleId);

            return $user;
        });

        $user->notify(new UserActivationNotification());

        $this->dispatchAssignedRoleEventsForUser(
            $user,
            RoleModificationContextEnum::USER_CREATION
        );

        return $user->fresh()->loadMissing('roles');
    }

    /**
     * Soft delete a user.
     */
    public function deleteUser(User $user): void
    {
        $this->userRepository->delete($user);
    }

    /**
     * Activate a user.
     */
    public function activateUser(User $user): User
    {
        $this->userRepository->update($user, [
            'status' => UserStatusEnum::ACTIVE->value,
        ]);

        return $user->fresh();
    }

    /**
     * Deactivate a user.
     */
    public function deactivateUser(User $user): User
    {
        $this->userRepository->update($user, [
            'status' => UserStatusEnum::INACTIVE->value,
        ]);

        return $user->fresh();
    }

    /**
     * Activate account using token and password.
     */
    public function activateAccount(string $token, string $password): User
    {
        $payload = $this->decodeActivationToken($token);
        $userId = $payload->sub ?? null;

        if (! $userId) {
            throw ValidationException::withMessages([
                'token' => ['Invalid activation token.'],
            ]);
        }

        $user = $this->userRepository->findByIdOrFail($userId, true);

        if ($user->trashed()) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or deleted account.'],
            ]);
        }

        if ($user->status === UserStatusEnum::ACTIVE->value) {
            throw ValidationException::withMessages([
                'email' => ['Account already activated.'],
            ]);
        }

        if ($user->status !== UserStatusEnum::PENDING->value) {
            throw ValidationException::withMessages([
                'email' => ['This account cannot be activated.'],
            ]);
        }

        $this->userRepository->update($user, [
            'password' => $password,
            'status' => UserStatusEnum::ACTIVE->value,
            'email_verified_at' => $user->email_verified_at ?: now(),
        ]);

        return $user->fresh()->loadMissing('roles');
    }

    /**
     * Change password.
     */
    public function changePassword(User $user, string $newPassword): void
    {
        $this->userRepository->updatePassword($user, $newPassword, false);

        $user->notify(new PasswordChangedNotification());

        $this->authService->logout();
    }

    /**
     * Update a user.
     */
    public function update(User $user, array $data): User
    {
        $this->updateUserDetails($user, $data);
        $this->syncUserRoles($user, $data);
        $this->updateUserAvatar($user, $data);

        return $user->fresh();
    }

    /**
     * Retrieve admin users with pagination.
     */
    public function getAdminUsersPaginated(array $filters = [])
    {
        return $this->userRepository->paginateAdmins($filters);
    }

    protected function dispatchAssignedRoleEventsForUser(
        User $user,
        ?RoleModificationContextEnum $context = null
    ): void {
        $user->loadMissing('roles');

        foreach ($user->roles as $role) {
            // Dispatch role modified event (role assigned)
            RoleModified::dispatch(
                $user,
                $role,
                RoleActionEnum::ASSIGNED,
                $this->resolveActor(),
                $context
            );
        }
    }

    protected function updateUserDetails(User $user, array $data): void
    {
        $payload = collect($data)
            ->except(['roles', 'avatar'])
            ->toArray();

        if (empty($payload)) {
            return;
        }

        $this->userRepository->update($user, $payload);
    }

    protected function syncUserRoles(User $user, array $data): void
    {
        if (! array_key_exists('roles', $data)) {
            return;
        }

        $beforeRoles = $this->currentRolesById($user);

        $this->userRepository->syncRoles($user, $data['roles']);

        $afterRoles = $this->freshRolesById($user);
        $actor = $this->resolveActor();

        $this->dispatchAssignedRoleEvents($user, $beforeRoles, $afterRoles, $actor);
        $this->dispatchRevokedRoleEvents($user, $beforeRoles, $afterRoles, $actor);
    }

    protected function updateUserAvatar(User $user, array $data): void
    {
        if (! array_key_exists('avatar', $data)) {
            return;
        }

        $this->handleAvatarUpdate($user, $data['avatar']);
    }

    protected function currentRolesById(User $user): Collection
    {
        return $user->roles()->get()->keyBy('id');
    }

    protected function freshRolesById(User $user): Collection
    {
        $user->load('roles');

        return $user->roles->keyBy('id');
    }

    protected function dispatchAssignedRoleEvents(
        User $user,
        Collection $beforeRoles,
        Collection $afterRoles,
        ?User $actor
    ): void {
        foreach ($afterRoles->diffKeys($beforeRoles) as $role) {
            // Dispatch role modified event (role assigned)
            RoleModified::dispatch($user, $role, RoleActionEnum::ASSIGNED, $actor, RoleModificationContextEnum::ROLE_SYNC);
        }
    }

    protected function dispatchRevokedRoleEvents(
        User $user,
        Collection $beforeRoles,
        Collection $afterRoles,
        ?User $actor
    ): void {
        foreach ($beforeRoles->diffKeys($afterRoles) as $role) {
            // Dispatch role modified event (role revoked)
            RoleModified::dispatch($user, $role, RoleActionEnum::REVOKED, $actor, RoleModificationContextEnum::ROLE_SYNC);
        }
    }

    protected function resolveActor(): ?User
    {
        $actor = Auth::user();

        return $actor instanceof User ? $actor : null;
    }

    /**
     * Create or restore a user.
     */
    protected function storeUser(array $data): User
    {
        $existingUser = $this->userRepository->findByEmail($data['email'], true);

        if ($existingUser) {
            return $this->handleExistingUser($existingUser, $data);
        }

        return $this->userRepository->create(array_merge([
            'password' => Str::random(12),
            'signup_source' => SignupSourceEnum::ADMIN->value
        ], $data));
    }

    /**
     * Handle existing user record.
     */
    protected function handleExistingUser(User $user, array $data): User
    {
        if ($user->trashed()) {
            $this->userRepository->restore($user);

            $this->userRepository->update($user, array_merge([
                'password' => Str::random(12),
            ], $data));

            return $user->fresh();
        }

        throw new ConflictException('User with this email already exists.');
    }

    /**
     * Decode activation token.
     */
    protected function decodeActivationToken(string $token): object
    {
        $secret = config('tokens.activation.secret');

        if (! is_string($secret) || trim($secret) === '') {
            throw ValidationException::withMessages([
                'token' => ['Activation token secret is not configured.'],
            ]);
        }

        try {
            $payload = JWT::decode($token, new Key($secret, 'HS256'));
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired activation link.'],
            ]);
        }

        if (($payload->purpose ?? null) !== 'account_activation') {
            throw ValidationException::withMessages([
                'token' => ['Invalid activation token.'],
            ]);
        }

        return $payload;
    }

    /**
     * Handle avatar update.
     */
    private function handleAvatarUpdate(User $user, mixed $avatar): void
    {
        if (is_null($avatar) || $avatar === '') {
            if (! empty($user->avatar)) {
                FileUploadHelper::deleteFromCloudinary($user->avatar);
            }

            $this->userRepository->update($user, ['avatar' => null]);
            return;
        }

        if (is_string($avatar) && $user->avatar && trim($avatar) === trim($user->avatar)) {
            return;
        }

        if ($avatar instanceof \Illuminate\Http\UploadedFile) {
            $newAvatar = FileUploadHelper::singleBinaryFileUpload($avatar, 'avatars', 'avatar_');
        } elseif (is_string($avatar)) {
            if (! preg_match('/^data:[^;]+;base64,/', $avatar)) {
                throw ValidationException::withMessages([
                    'avatar' => ['Invalid avatar string format.'],
                ]);
            }

            $newAvatar = FileUploadHelper::singleStringFileUpload($avatar, 'avatars', 'avatar_');
        } else {
            throw ValidationException::withMessages([
                'avatar' => ['Invalid avatar format provided.'],
            ]);
        }

        if (! empty($user->avatar)) {
            FileUploadHelper::deleteFromCloudinary($user->avatar);
        }

        $this->userRepository->update($user, ['avatar' => $newAvatar]);
    }
}