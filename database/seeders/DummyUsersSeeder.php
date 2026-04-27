<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Enums\UserRelationsEnum;
use App\Models\User;
use App\Models\UserRelation;
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
            ['name' => 'Annotator Eva',   'username' => 'annotator_eva',   'email' => 'annotator.eva@example.com'],
            ['name' => 'Annotator Frank', 'username' => 'annotator_frank', 'email' => 'annotator.frank@example.com'],
            ['name' => 'Annotator Grace', 'username' => 'annotator_grace', 'email' => 'annotator.grace@example.com'],
            ['name' => 'Annotator Henry', 'username' => 'annotator_henry', 'email' => 'annotator.henry@example.com'],
            ['name' => 'Annotator Ivy',   'username' => 'annotator_ivy',   'email' => 'annotator.ivy@example.com'],
            ['name' => 'Annotator Jack',  'username' => 'annotator_jack',  'email' => 'annotator.jack@example.com'],
            ['name' => 'Annotator Karen', 'username' => 'annotator_karen', 'email' => 'annotator.karen@example.com'],
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

        // Connect each annotator to 2 managers / admins.
        $alice = User::query()->where('email', 'admin.alice@example.com')->firstOrFail();
        $bob = User::query()->where('email', 'admin.bob@example.com')->firstOrFail();
        $carol = User::query()->where('email', 'manager.carol@example.com')->firstOrFail();
        $dave = User::query()->where('email', 'manager.dave@example.com')->firstOrFail();

        $annotatorRelations = [
            'annotator.eva@example.com' => [$carol, $dave],
            'annotator.frank@example.com' => [$alice, $carol],
            'annotator.grace@example.com' => [$bob,   $dave],
            'annotator.henry@example.com' => [$alice, $bob],
            'annotator.ivy@example.com' => [$carol, $bob],
            'annotator.jack@example.com' => [$alice, $dave],
            'annotator.karen@example.com' => [$bob,   $carol],
        ];

        foreach ($annotatorRelations as $email => $supervisors) {
            $annotator = User::query()->where('email', $email)->firstOrFail();
            foreach ($supervisors as $supervisor) {
                UserRelation::query()->firstOrCreate([
                    'user_id' => $annotator->getKey(),
                    'related_user_id' => $supervisor->getKey(),
                    'relation_type' => UserRelationsEnum::ANNOTATOR_OF_MANAGER,
                ]);
            }
        }
    }
}
