<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetAllAnnotatorsQuery {
    /**
     * @return Collection<int, User>
     */
    public function get(): Collection {
        return User::query()
            ->whereHas('roles', fn (Builder $q) => $q->where('name', RolesEnum::ANNOTATOR->value))
            ->get();
    }
}
