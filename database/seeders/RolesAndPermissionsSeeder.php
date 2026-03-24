<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Enums\RolesEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder {
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void {
        /**
         * NOTICE: If you have CACHE_STORE=database set in your .env,
         * remember that you must install Laravel's cache tables via a migration before performing any cache operations.
         */

        // flush cache before creating roles and permissions
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // Create permissions
        $permissions = collect(PermissionsEnum::cases())
            ->map(fn ($permission) => Permission::query()->firstOrCreate(['name' => $permission->value]));

        // create roles using RolesEnum
        $adminRole = Role::query()->firstOrCreate(['name' => RolesEnum::ADMIN->value, 'guard_name' => 'web']);
        $annotationManagerRole = Role::query()->firstOrCreate(['name' => RolesEnum::ANNOTATION_MANAGER->value, 'guard_name' => 'web']);
        Role::query()->firstOrCreate(['name' => RolesEnum::ANNOTATOR->value, 'guard_name' => 'web']);

        // flush cache after creating roles and permissions
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // Annotation managers can manage users
        $annotationManagerRole->givePermissionTo([
            PermissionsEnum::CREATE_ANNOTATORS->value,
            PermissionsEnum::MANAGE_ANNOTATORS->value,
            PermissionsEnum::CREATE_MANAGERS->value,
            PermissionsEnum::MANAGE_MANAGERS->value,
            PermissionsEnum::CREATE_PROJECTS->value,
            PermissionsEnum::MANAGE_PROJECTS->value,
            PermissionsEnum::CONNECT_MANAGERS_TO_PROJECTS->value,
            PermissionsEnum::CONNECT_ANNOTATORS_TO_PROJECTS->value,
            PermissionsEnum::CONNECT_ANNOTATORS_TO_MANAGERS->value,
            PermissionsEnum::CREATE_ADMINS->value,
            PermissionsEnum::MANAGE_ADMINS->value,
            PermissionsEnum::CONNECT_MANAGERS_TO_TASKS->value,
        ]);

        // Admin gets all permissions
        $adminRole->givePermissionTo($permissions);
    }
}
