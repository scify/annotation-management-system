<?php

declare(strict_types=1);

namespace App\Queries\User;

use App\Models\User;

final readonly class FindNextDeletionIndexQuery {
    public function find(string $originalUsername): int {
        $x = 1;
        while (User::withTrashed()->where('username', $originalUsername . '_del_' . $x)->exists()) {
            $x++;
        }

        return $x;
    }
}
