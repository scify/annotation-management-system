<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyUsersSeeder extends Seeder {
    public function run(): void {
        $password = config('app.default_user_password_for_seeder');

        $admins = [
            ['name' => 'Admin Alice', 'username' => 'admin_alice', 'email' => 'admin.alice@example.com'],
            ['name' => 'Admin Bob', 'username' => 'admin_bob', 'email' => 'admin.bob@example.com'],
        ];

        foreach ($admins as $data) {
            $user = User::query()->updateOrCreate(['email' => $data['email']], [
                'name' => $data['name'],
                'username' => $data['username'],
                'is_active' => true,
                'password' => Hash::make($password),
            ]);
            $user->syncRoles([RolesEnum::ADMIN->value]);
        }

        $managers = [
            ['name' => 'Manager Carol', 'username' => 'manager_carol', 'email' => 'manager.carol@example.com'],
            ['name' => 'Manager Dave', 'username' => 'manager_dave', 'email' => 'manager.dave@example.com'],
        ];

        foreach ($managers as $data) {
            $user = User::query()->updateOrCreate(['email' => $data['email']], [
                'name' => $data['name'],
                'username' => $data['username'],
                'is_active' => true,
                'password' => Hash::make($password),
            ]);
            $user->syncRoles([RolesEnum::ANNOTATION_MANAGER->value]);
        }

        $annotators = [
            ['name' => 'Annotator Eva', 'username' => 'annotator_eva', 'email' => 'annotator.eva@example.com'],
            ['name' => 'Annotator Frank', 'username' => 'annotator_frank', 'email' => 'annotator.frank@example.com'],
        ];

        foreach ($annotators as $data) {
            $user = User::query()->updateOrCreate(['email' => $data['email']], [
                'name' => $data['name'],
                'username' => $data['username'],
                'is_active' => true,
                'password' => Hash::make($password),
            ]);
            $user->syncRoles([RolesEnum::ANNOTATOR->value]);
        }
    }
}
