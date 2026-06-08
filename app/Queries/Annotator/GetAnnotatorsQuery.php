<?php

declare(strict_types=1);

namespace App\Queries\Annotator;

use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetAnnotatorsQuery {
    /**
     * @param  array<int, mixed>|null  $ids
     *
     * @return Collection<int, User>
     */
    public function getActive(?array $ids = null): Collection {
        return $this->query($ids)->whereIn('status', [StatusEnum::ACTIVE, StatusEnum::PENDING])->get();
    }

    /**
     * @param  array<int, mixed>|null  $ids
     *
     * @return Collection<int, User>
     */
    public function getAll(?array $ids = null): Collection {
        return $this->query($ids)->get();
    }

    /**
     * @param  array<int, int>  $excludeIds
     *
     * @return Collection<int, User>
     */
    public function getAllExcluding(array $excludeIds): Collection {
        return $this->query()
            ->when($excludeIds !== [], fn (Builder $q) => $q->whereNotIn('id', $excludeIds))
            ->get();
    }

    /**
     * @param  array<int, mixed>|null  $ids
     *
     * @return Builder<User>
     */
    private function query(?array $ids = null): Builder {
        return User::query()
            ->whereHas('roles', fn (Builder $q) => $q->where('name', RolesEnum::ANNOTATOR->value))
            ->when($ids !== null, fn ($q) => $q->whereIn('id', $ids))
            ->select(['id', 'name', 'username', 'status'])
            ->without('roles');
    }
}
