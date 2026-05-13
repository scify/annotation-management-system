<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final readonly class GetInProgressProjectsQuery {
    /**
     * @return Collection<int, Project>
     */
    public function get(): Collection {
        return Project::query()
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->select(['id', 'name', 'owner_user_id', 'annotation_task_id', 'status', 'dataset_id', 'started_at', 'deadline_at'])
            ->addSelect(DB::raw(
                '(SELECT COUNT(DISTINCT aom.annotator_id)
                  FROM annotator_of_managers aom
                  WHERE aom.manager_id = projects.owner_user_id
                     OR aom.manager_id IN (SELECT user_id FROM comanagers WHERE project_id = projects.id)
                ) as annotators_count'
            ))
            ->with(['annotationTask:id,title', 'dataset:id,name', 'owner:id,username', 'comanagerRecords.user:id,username'])
            ->withCount(['subProjects as subprojects_count'])
            ->get();
    }
}
