<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetInProgressProjectsQuery {
    /**
     * @return Collection<int, Project>
     */
    public function get(): Collection {
        return Project::query()
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->select(['id', 'name', 'owner_user_id', 'annotation_task_id', 'status', 'dataset_id', 'started_at', 'deadline_at'])
            ->with(['annotationTask:id,title', 'dataset:id,name', 'owner:id,username', 'coManagerRelations.user:id,username'])
            ->withCount(['subProjects as subprojects_count', 'annotatorRelations as annotators_count'])
            ->get();
    }
}
