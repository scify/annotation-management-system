<?php

declare(strict_types=1);

namespace App\Queries\User;

use App\Models\User;

final readonly class FindUserByUsernameQuery {
    public function exists(string $username): bool {
        return User::withTrashed()->where('username', $username)->exists();
    }
}
