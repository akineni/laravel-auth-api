<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\{Role, Permission, User};
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Get default guard dynamically
        $defaultGuard = config('auth.defaults.guard', 'api');

        // Default CRUD actions
        $defaultActions = ['view', 'create', 'edit', 'delete'];

        $modules = [
            'user_management' => $defaultActions,
        ];

        // Seed General Permissions
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => $defaultGuard,
                ]);
            }
        }

        /**
         * Create Roles (idempotent)
         */
        $superAdmin = Role::firstOrCreate([
            'name' => RoleEnum::SUPER_ADMIN->value,
            'guard_name' => $defaultGuard,
        ]);

        // Assign Permissions
        $superAdmin->syncPermissions(
            Permission::where('guard_name', $defaultGuard)->get()
        );

        if ($user = User::first()) {
            $user->assignRole($superAdmin);
            $this->command->info("User '{$user->email}' assigned to role '{$superAdmin->name}'");
        } else {
            $this->command->warn('No users found to assign Super Admin role.');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('Roles and permissions seeded successfully!');
    }
}