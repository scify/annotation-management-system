<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\ProjectManager;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetProjectsManagedByUserQuery {
    /**
     * @return Collection<int, Project>
     */
    public function get(int $userId, ?ProjectStatusEnum $status = null): Collection {
        return Project::query()
            ->whereIn('id', ProjectManager::query()->where('user_id', $userId)->select('project_id'))
            ->when($status instanceof ProjectStatusEnum, fn ($q) => $q->where('status', $status))
            ->select(['id', 'name', 'owner_user_id', 'annotation_task_id', 'dataset_id', 'status', 'started_at', 'completed_at', 'scheduled_at', 'deadline_at'])
            ->withCount(['annotators as annotators_count'])
            ->with(['annotationTask:id,title', 'dataset:id,name', 'owner:id,username', 'projectManagers.user:id,username'])
            ->with(['subProjects:id,project_id'])
            ->get();
    }
}
