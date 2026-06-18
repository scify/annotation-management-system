<?php

declare(strict_types=1);

namespace App\Queries\User;

use App\Models\User;

final readonly class GetUsernamesByIdsQuery {
    /**
     * @param  array<int, int>  $ids
     *
     * @return array<int, string>
     */
    public function get(array $ids): array {
        if ($ids === []) {
            return [];
        }

        /** @var array<int, string> */
        return User::query()
            ->whereIn('id', $ids)
            ->pluck('username')
            ->all();
    }
}
