<?php

declare(strict_types=1);

namespace App\Queries\Manager;

use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetCoManagersQuery {
    /**
     * @param  array<int, mixed>|null  $ids  Restrict to specific IDs; null returns all active co-managers.
     *
     * @return Collection<int, User>
     */
    public function get(?array $ids = null): Collection {
        return User::query()
            ->whereIn('status', [StatusEnum::ACTIVE, StatusEnum::PENDING])
            ->whereHas('roles', fn (Builder $q) => $q->whereIn('name', [
                RolesEnum::ADMIN->value,
                RolesEnum::ANNOTATION_MANAGER->value,
            ]))
            ->when($ids !== null, fn ($q) => $q->whereIn('id', $ids))
            ->select(['id', 'username', 'name', 'status'])
            ->get();
    }
}
