<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Enums\UserRelationsEnum;
use App\Models\Project;
use App\Models\UserRelation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetUserInProgressProjectsQuery {
    /**
     * @return Collection<int, Project>
     */
    public function get(int $userId): Collection {
        $collaboratorOwnerIds = UserRelation::query()
            ->where('related_user_id', $userId)
            ->where('relation_type', UserRelationsEnum::COLLABORATOR_OF_USER)
            ->select('user_id');

        return Project::query()
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->where(function (Builder $query) use ($userId, $collaboratorOwnerIds): void {
                $query->where('owner_user_id', $userId)
                    ->orWhereIn('owner_user_id', $collaboratorOwnerIds);
            })
            ->select(['id', 'name', 'owner_user_id', 'annotation_task_id', 'dataset_id', 'status', 'started_at', 'deadline_at'])
            ->with(['annotationTask:id,title', 'dataset:id,name', 'owner:id,username', 'coManagerRelations.user:id,username'])
            ->withCount(['subProjects as subprojects_count', 'annotatorRelations as annotators_count'])
            ->get();
    }
}
