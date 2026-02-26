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
            'name' => 'Admin User',
            'password' => Hash::make($password),
        ]);
        $admin->assignRole(RolesEnum::ADMINISTRATOR->value);

        // Create or update the user manager
        $userManager = User::query()->updateOrCreate(['email' => 'user_manager@scify.org'], [
            'name' => 'User Manager',
            'password' => Hash::make($password),
        ]);
        $userManager->assignRole(RolesEnum::USER_MANAGER->value);

        // Create or update the registered user
        $registeredUser = User::query()->updateOrCreate(['email' => 'registered_user@scify.org'], [
            'name' => 'Registered User',
            'password' => Hash::make($password),
        ]);
        $registeredUser->assignRole(RolesEnum::REGISTERED_USER->value);
    }
}
