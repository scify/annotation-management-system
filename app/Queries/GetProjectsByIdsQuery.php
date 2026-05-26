<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetProjectsByIdsQuery {
    /**
     * @param  array<int, mixed>  $projectIds
     *
     * @return Collection<int, Project>
     */
    public function get(array $projectIds, ?ProjectStatusEnum $status = null): Collection {
        if ($projectIds === []) {
            return new Collection();
        }

        return Project::query()
            ->whereIn('id', $projectIds)
            ->when($status instanceof ProjectStatusEnum, fn ($q) => $q->where('status', $status))
            ->with([
                'owner:id,username',
                'annotationTask:id,title',
                'dataset:id,name',
                'projectManagers.user:id,username,email,status',
                'subProjects:id,project_id,name,status,scheduled_at,deadline_at,started_at,completed_at,first_instance_index,last_instance_index',
            ])
            ->get();
    }
}
