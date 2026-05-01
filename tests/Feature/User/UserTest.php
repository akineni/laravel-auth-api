<?php

namespace Tests\Feature\User;

use App\Enums\UserStatusEnum;
use App\Models\Role;
use App\Models\User;
use App\Notifications\AccountActivatedNotification;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    // -------------------------------------------------------
    // Current User
    // -------------------------------------------------------

    public function test_authenticated_user_can_get_their_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)
            ->getJson('/api/v1/users/me');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/v1/users/me');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------
    // List Users
    // -------------------------------------------------------

    public function test_admin_can_list_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->actingAsUser($this->admin)
            ->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_non_admin_cannot_list_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)
            ->getJson('/api/v1/users');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------
    // Show User
    // -------------------------------------------------------

    public function test_admin_can_view_a_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($this->admin)
            ->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.email', $user->email);
    }

    // -------------------------------------------------------
    // Create User
    // -------------------------------------------------------

    public function test_admin_can_create_a_user(): void
    {
        Notification::fake();

        $role = Role::create(['name' => 'Editor', 'guard_name' => 'api']);

        $response = $this->actingAsUser($this->admin)
            ->postJson('/api/v1/users', [
                'firstname'        => 'Jane',
                'lastname'         => 'Doe',
                'email'            => 'jane@example.com',
                'assigned_role_id' => $role->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    }

    public function test_non_admin_cannot_create_a_user(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'api']);

        $response = $this->actingAsUser($user)
            ->postJson('/api/v1/users', [
                'firstname'        => 'Jane',
                'lastname'         => 'Doe',
                'email'            => 'jane@example.com',
                'assigned_role_id' => $role->id,
            ]);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------
    // Update User
    // -------------------------------------------------------

    public function test_admin_can_update_a_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($this->admin)
            ->patchJson("/api/v1/users/{$user->id}", [
                'firstname' => 'Updated',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id'        => $user->id,
            'firstname' => 'Updated',
        ]);
    }

    // -------------------------------------------------------
    // Delete User
    // -------------------------------------------------------

    public function test_admin_can_delete_a_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($this->admin)
            ->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    // -------------------------------------------------------
    // Activate / Deactivate
    // -------------------------------------------------------

    public function test_admin_can_activate_a_user(): void
    {
        Notification::fake();

        $user = User::factory()->inactive()->create();

        $response = $this->actingAsUser($this->admin)
            ->patchJson("/api/v1/users/{$user->id}/activate");

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id'     => $user->id,
            'status' => UserStatusEnum::ACTIVE->value,
        ]);

        Notification::assertSentTo($user, AccountActivatedNotification::class);
    }

    public function test_admin_can_deactivate_a_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($this->admin)
            ->patchJson("/api/v1/users/{$user->id}/deactivate");

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id'     => $user->id,
            'status' => UserStatusEnum::INACTIVE->value,
        ]);
    }

    // -------------------------------------------------------
    // Update Profile (me)
    // -------------------------------------------------------

    public function test_user_can_update_their_own_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)
            ->patchJson('/api/v1/users/me', [
                'firstname' => 'NewName',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id'        => $user->id,
            'firstname' => 'NewName',
        ]);
    }

    // -------------------------------------------------------
    // Change Password
    // -------------------------------------------------------

    public function test_user_can_change_their_password(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->actingAsUser($user)
            ->patchJson('/api/v1/users/me/password', [
                'current_password'          => 'T3st#Secure!XyZ9',
                'new_password'              => 'N3w#Uniq!P@ssXyZ',
                'new_password_confirmation' => 'N3w#Uniq!P@ssXyZ',
            ]);

        $response->assertStatus(200);
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)
            ->patchJson('/api/v1/users/me/password', [
                'current_password'          => 'WrongPassword999!',
                'new_password'              => 'N3w#Uniq!P@ssXyZ',
                'new_password_confirmation' => 'N3w#Uniq!P@ssXyZ',
            ]);

        $response->assertStatus(422);
    }

}