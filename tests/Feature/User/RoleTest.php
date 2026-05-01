<?php

namespace Tests\Feature\User;

use App\Models\Role;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleTest extends TestCase
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
    // List Roles
    // -------------------------------------------------------

    public function test_admin_can_list_roles(): void
    {
        $response = $this->actingAsUser($this->admin)
            ->getJson('/api/v1/roles');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_non_admin_cannot_list_roles(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)
            ->getJson('/api/v1/roles');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------
    // Create Role
    // -------------------------------------------------------

    public function test_admin_can_create_a_role(): void
    {
        $response = $this->actingAsUser($this->admin)
            ->postJson('/api/v1/roles', [
                'name' => 'Editor',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Editor');

        $this->assertDatabaseHas('roles', ['name' => 'Editor']);
    }

    public function test_create_role_rejects_duplicate_name(): void
    {
        Role::create(['name' => 'Editor', 'guard_name' => 'api']);

        $response = $this->actingAsUser($this->admin)
            ->postJson('/api/v1/roles', [
                'name' => 'Editor',
            ]);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------
    // Update Role
    // -------------------------------------------------------

    public function test_admin_can_update_a_role(): void
    {
        $role = Role::create(['name' => 'Writer', 'guard_name' => 'api']);

        $response = $this->actingAsUser($this->admin)
            ->patchJson("/api/v1/roles/{$role->id}", [
                'name' => 'Senior Writer',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Senior Writer');
    }

    // -------------------------------------------------------
    // Delete Role
    // -------------------------------------------------------

    public function test_admin_can_delete_a_role(): void
    {
        $role = Role::create(['name' => 'Temp Role', 'guard_name' => 'api']);

        $response = $this->actingAsUser($this->admin)
            ->deleteJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('roles', ['name' => 'Temp Role']);
    }

    // -------------------------------------------------------
    // List Permissions
    // -------------------------------------------------------

    public function test_admin_can_list_permissions(): void
    {
        $response = $this->actingAsUser($this->admin)
            ->getJson('/api/v1/roles/permissions');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    // -------------------------------------------------------
    // Assign / Revoke Role on User
    // -------------------------------------------------------

    public function test_admin_can_assign_a_role_to_a_user(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'api']);

        $response = $this->actingAsUser($this->admin)
            ->postJson("/api/v1/users/{$user->id}/roles/assign", [
                'role_id' => $role->id,
            ]);

        $response->assertStatus(200);

        $this->assertTrue($user->fresh()->hasRole('Editor'));
    }

    public function test_admin_can_revoke_a_role_from_a_user(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'api']);
        $user->assignRole($role);

        $response = $this->actingAsUser($this->admin)
            ->postJson("/api/v1/users/{$user->id}/roles/revoke", [
                'role_id' => $role->id,
            ]);

        $response->assertStatus(200);

        $this->assertFalse($user->fresh()->hasRole('Editor'));
    }

    public function test_assigning_already_assigned_role_returns_conflict(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'api']);
        $user->assignRole($role);

        $response = $this->actingAsUser($this->admin)
            ->postJson("/api/v1/users/{$user->id}/roles/assign", [
                'role_id' => $role->id,
            ]);

        $response->assertStatus(409);
    }

    public function test_revoking_unassigned_role_returns_conflict(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'api']);

        $response = $this->actingAsUser($this->admin)
            ->postJson("/api/v1/users/{$user->id}/roles/revoke", [
                'role_id' => $role->id,
            ]);

        $response->assertStatus(409);
    }

}