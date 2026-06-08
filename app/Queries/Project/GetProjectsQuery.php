<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetProjectsQuery {
    /**
     * @return array<int, mixed>
     */
    public function getIds(?ProjectStatusEnum $status = null): array {
        return Project::query()
            ->when($status instanceof ProjectStatusEnum, fn ($q) => $q->where('status', $status))
            ->pluck('id')
            ->all();
    }

    /**
     * @return Collection<int, Project>
     */
    public function get(?ProjectStatusEnum $status = null): Collection {
        return Project::query()
            ->when($status instanceof ProjectStatusEnum, fn ($q) => $q->where('status', $status))
            ->select(['id', 'name', 'owner_user_id', 'annotation_task_id', 'status', 'dataset_id', 'started_at', 'completed_at', 'scheduled_at', 'deadline_at'])
            ->withCount(['annotators as annotators_count'])
            ->with(['annotationTask:id,title', 'dataset:id,name', 'owner:id,username', 'projectManagers.user:id,username'])
            ->with(['subProjects:id,project_id'])
            ->get();
    }
}
