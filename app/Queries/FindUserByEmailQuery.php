<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\User;

final readonly class FindUserByEmailQuery {
    public function get(string $email): ?User {
        return User::query()->where('email', $email)->first();
    }
}
