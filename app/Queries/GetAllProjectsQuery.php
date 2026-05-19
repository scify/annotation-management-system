<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetAllProjectsQuery {
    /**
     * @return Collection<int, Project>
     */
    public function get(): Collection {
        return Project::query()
            ->select(['id', 'name', 'owner_user_id', 'annotation_task_id', 'status', 'dataset_id', 'started_at', 'completed_at', 'scheduled_at', 'deadline_at'])
            ->withCount(['annotators as annotators_count'])
            ->with(['annotationTask:id,title', 'dataset:id,name', 'owner:id,username', 'projectManagers.user:id,username'])
            ->withCount(['subProjects as subprojects_count'])
            ->get();
    }
}
