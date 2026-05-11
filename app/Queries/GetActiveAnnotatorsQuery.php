<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetActiveAnnotatorsQuery {
    /**
     * @return Collection<int, User>
     */
    public function get(): Collection {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $q) => $q->where('name', RolesEnum::ANNOTATOR->value))
            ->select(['id', 'name'])
            ->without('roles')
            ->get();
    }
}
