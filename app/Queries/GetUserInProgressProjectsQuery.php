<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\ProjectManager;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetUserInProgressProjectsQuery {
    /**
     * @return Collection<int, Project>
     */
    public function get(int $userId): Collection {
        return Project::query()
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->whereIn('id', ProjectManager::query()->where('user_id', $userId)->select('project_id'))
            ->select(['id', 'name', 'owner_user_id', 'annotation_task_id', 'dataset_id', 'status', 'started_at', 'deadline_at'])
            ->withCount(['annotators as annotators_count'])
            ->with(['annotationTask:id,title', 'dataset:id,name', 'owner:id,username', 'projectManagers.user:id,username'])
            ->withCount(['subProjects as subprojects_count'])
            ->get();
    }
}
