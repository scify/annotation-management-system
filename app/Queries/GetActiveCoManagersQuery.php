<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetActiveCoManagersQuery {
    /**
     * @return Collection<int, User>
     */
    public function get(): Collection {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $q) => $q->whereIn('name', [
                RolesEnum::ADMIN->value,
                RolesEnum::ANNOTATION_MANAGER->value,
            ]))
            ->select(['id', 'username', 'name'])
            ->get();
    }
}
