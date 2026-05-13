<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Models\Comanager;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final readonly class GetUserInProgressProjectsQuery {
    /**
     * @return Collection<int, Project>
     */
    public function get(int $userId): Collection {
        return Project::query()
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->where(function (Builder $query) use ($userId): void {
                $query->where('owner_user_id', $userId)
                    ->orWhereIn('id', Comanager::query()->where('user_id', $userId)->select('project_id'));
            })
            ->select(['id', 'name', 'owner_user_id', 'annotation_task_id', 'dataset_id', 'status', 'started_at', 'deadline_at'])
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
