<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetInProgressProjectsByIdsQuery {
    /**
     * @param  array<int, mixed>  $projectIds
     *
     * @return Collection<int, Project>
     */
    public function get(array $projectIds): Collection {
        if ($projectIds === []) {
            return new Collection();
        }

        return Project::query()
            ->whereIn('id', $projectIds)
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->with([
                'owner:id,username',
                'annotationTask:id,title',
                'dataset:id,name',
                'projectManagers.user:id,username',
            ])
            ->get();
    }
}
