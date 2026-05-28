<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Models\SubProject;
use Illuminate\Support\Collection;

final readonly class GetSubProjectIdsQuery {
    /**
     * @param  array<int, mixed>  $projectIds
     *
     * @return Collection<int, int>
     */
    public function get(array $projectIds, ?ProjectStatusEnum $status = null): Collection {
        if ($projectIds === []) {
            return collect();
        }

        /** @var Collection<int, int> $result */
        $result = SubProject::query()
            ->whereIn('project_id', $projectIds)
            ->when($status instanceof ProjectStatusEnum, fn ($q) => $q->where('status', $status))
            ->pluck('id');

        return $result;
    }

    /**
     * Returns all subproject IDs, optionally filtered by status.
     *
     * @return Collection<int, int>
     */
    public function getAll(?ProjectStatusEnum $status = null): Collection {
        /** @var Collection<int, int> $result */
        $result = SubProject::query()
            ->when($status instanceof ProjectStatusEnum, fn ($q) => $q->where('status', $status))
            ->pluck('id');

        return $result;
    }
}
