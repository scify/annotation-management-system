<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetActiveAnnotatorsByIdsQuery {
    /**
     * @param  array<int, mixed>  $ids
     *
     * @return Collection<int, User>
     */
    public function get(array $ids): Collection {
        return User::query()
            ->where('is_active', true)
            ->whereIn('id', $ids)
            ->select(['id', 'name'])
            ->without('roles')
            ->get();
    }
}
