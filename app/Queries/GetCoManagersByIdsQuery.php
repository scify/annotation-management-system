<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetCoManagersByIdsQuery {
    /**
     * @param  array<int, mixed>  $ids
     *
     * @return Collection<int, User>
     */
    public function get(array $ids): Collection {
        return User::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $q) => $q->whereIn('name', [
                RolesEnum::ADMIN->value,
                RolesEnum::ANNOTATION_MANAGER->value,
            ]))
            ->select(['id', 'username', 'name'])
            ->get();
    }
}
