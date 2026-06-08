<?php

declare(strict_types=1);

namespace App\Queries\User;

use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Models\User;

final readonly class CreateAnnotatorQuery {
    public function create(string $name, string $username, string $password): User {
        $user = User::query()->create([
            'name' => $name,
            'username' => $username,
            'password' => $password,
            'status' => StatusEnum::PENDING,
        ]);

        $user->syncRoles([RolesEnum::ANNOTATOR]);

        return $user;
    }
}
