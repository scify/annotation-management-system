<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Models\SubProject;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetInProgressSubProjectsByProjectsQuery {
    /**
     * @param  array<int, mixed>  $projectIds
     *
     * @return Collection<int, SubProject>
     */
    public function get(array $projectIds): Collection {
        if ($projectIds === []) {
            return new Collection();
        }

        return SubProject::query()
            ->whereIn('project_id', $projectIds)
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->get();
    }
}
