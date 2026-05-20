<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Models\SubProject;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetSubProjectsOfProjectsQuery {
    /**
     * @param  array<int, mixed>  $projectIds
     *
     * @return Collection<int, SubProject>
     */
    public function get(array $projectIds, ?ProjectStatusEnum $status = null): Collection {
        if ($projectIds === []) {
            return new Collection();
        }

        return SubProject::query()
            ->whereIn('project_id', $projectIds)
            ->when($status instanceof ProjectStatusEnum, fn ($q) => $q->where('status', $status))
            ->get();
    }
}
