<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\User;

final readonly class FindUserByNameQuery {
    public function exists(string $name): bool {
        return User::withTrashed()->where('name', $name)->exists();
    }
}
