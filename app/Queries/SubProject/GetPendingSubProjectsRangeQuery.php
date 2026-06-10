<?php

declare(strict_types=1);

namespace App\Queries\SubProject;

use App\Enums\ProjectStatusEnum;
use App\Models\SubProject;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetPendingSubProjectsRangeQuery {
    /**
     * @param  array<int, int>  $subProjectIds
     *
     * @return Collection<int, SubProject>
     */
    public function get(array $subProjectIds): Collection {
        if ($subProjectIds === []) {
            return new Collection();
        }

        return SubProject::query()
            ->whereIn('id', $subProjectIds)
            ->where('status', ProjectStatusEnum::PENDING)
            ->select('id', 'first_instance_index', 'last_instance_index')
            ->get();
    }
}
