<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Models\User;

final readonly class CreateAdminQuery {
    public function create(string $name, string $username, string $email, string $password): User {
        $user = User::query()->create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'status' => StatusEnum::PENDING,
        ]);

        $user->syncRoles([RolesEnum::ADMIN]);

        return $user;
    }
}
