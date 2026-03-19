<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $password = config('app.default_user_password_for_seeder');

        // Create or update the admin user
        $admin = User::query()->updateOrCreate(['email' => 'admin@scify.org'], [
            'name' => 'SciFY Admin',
            'username' => 'scifyadmin',
            'is_active' => true,
            'password' => Hash::make($password),
        ]);
        $admin->syncRoles([RolesEnum::ADMIN->value]);

        // Create or update the annotation manager
        $annotationManager = User::query()->updateOrCreate(['email' => 'annotation_manager@scify.org'], [
            'name' => 'SciFY Annotation Manager',
            'username' => 'scifymanager',
            'is_active' => true,
            'password' => Hash::make($password),
        ]);
        $annotationManager->syncRoles([RolesEnum::ANNOTATION_MANAGER->value]);

        // Create or update the annotator
        $annotator = User::query()->updateOrCreate(['email' => 'annotator@scify.org'], [
            'name' => 'SciFY Annotator',
            'username' => 'scifyannotator',
            'is_active' => true,
            'password' => Hash::make($password),
        ]);
        $annotator->syncRoles([RolesEnum::ANNOTATOR->value]);
    }
}
