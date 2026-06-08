<?php

declare(strict_types=1);

namespace App\Queries\User;

use App\Models\User;

final readonly class FindUserByEmailQuery {
    public function exists(string $email): bool {
        return User::withTrashed()->where('email', $email)->exists();
    }

    public function get(string $email): ?User {
        return User::query()->where('email', $email)->first();
    }
}
