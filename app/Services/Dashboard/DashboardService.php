<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\ProjectStatusEnum;
use App\Enums\UserRelationsEnum;
use App\Models\AnnotationTask;
use App\Models\Dataset;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Database\Eloquent\Builder;

class DashboardService {
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllInProgressProjects(): array {
        $dashboard_project_data = Project::query()
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->select(['id', 'name', 'owner_user_id', 'annotation_task_id', 'dataset_id', 'deadline_at'])
            ->get()
            ->map(fn (Project $project) => $project->makeHidden(['is_delayed_to_start', 'is_delayed_to_end'])->toArray())
            ->values()
            ->all();

        $this->augmentProjectData($dashboard_project_data);

        return $dashboard_project_data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMyInProgressProjects(int $userId): array {
        $collaboratorOwnerIds = UserRelation::query()->where('related_user_id', $userId)
            ->where('relation_type', UserRelationsEnum::COLLABORATOR_OF_USER)
            ->select('user_id');

        $dashboard_project_data = Project::query()->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->where(function (Builder $query) use ($userId, $collaboratorOwnerIds): void {
                $query->where('owner_user_id', $userId)
                    ->orWhereIn('owner_user_id', $collaboratorOwnerIds);
            })
            ->select(['id', 'name', 'owner_user_id', 'annotation_task_id', 'dataset_id', 'deadline_at'])
            ->get()
            ->map(fn (Project $project) => $project->makeHidden(['is_delayed_to_start', 'is_delayed_to_end'])->toArray())
            ->values()
            ->all();

        $this->augmentProjectData($dashboard_project_data);

        return $dashboard_project_data;
    }

    /**
     * @param  array<int, array<string, mixed>>  $dashboard_project_data
     */
    protected function augmentWithManagers(array &$dashboard_project_data): void {
        $ownerIds = array_column($dashboard_project_data, 'owner_user_id');
        $owners = User::query()->whereIn('id', $ownerIds)->get()->keyBy('id');

        $projectIds = array_column($dashboard_project_data, 'id');
        $coManagerRelations = UserRelation::query()
            ->whereIn('project_id', $projectIds)
            ->where('relation_type', UserRelationsEnum::COLLABORATOR_OF_USER)
            ->get();

        $coManagerUsers = User::query()
            ->whereIn('id', $coManagerRelations->pluck('user_id')->unique()->toArray())
            ->get()
            ->keyBy('id');

        $coManagersByProject = $coManagerRelations->groupBy('project_id');

        foreach ($dashboard_project_data as &$project) {
            $owner = $owners->get((int) $project['owner_user_id']);
            $project['owner_name'] = $owner instanceof User ? $owner->username : null;

            $project['co_managers'] = ($coManagersByProject->get((int) $project['id']) ?? collect())
                ->map(function (UserRelation $relation) use ($coManagerUsers): ?array {
                    $user = $coManagerUsers->get((int) $relation->user_id);

                    return $user instanceof User
                        ? ['id' => $user->id, 'username' => $user->username]
                        : null;
                })
                ->filter()
                ->values()
                ->all();
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $dashboard_project_data
     */
    protected function augmentWithProgress(array &$dashboard_project_data): void {
        foreach ($dashboard_project_data as &$project) {
            $project['project_progress'] = 0.5;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $dashboard_project_data
     */
    protected function augmentWithNotifications(array &$dashboard_project_data): void {
        foreach ($dashboard_project_data as &$project) {
            $project['notifications_count'] = 0;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $dashboard_project_data
     */
    protected function augmentWithSubprojects(array &$dashboard_project_data): void {
        $projectIds = array_column($dashboard_project_data, 'id');
        $counts = SubProject::query()
            ->whereIn('project_id', $projectIds)
            ->selectRaw('project_id, COUNT(*) as count')
            ->groupBy('project_id')
            ->pluck('count', 'project_id');

        foreach ($dashboard_project_data as &$project) {
            $project['subprojects_count'] = (int) ($counts->get((int) $project['id']) ?? 0);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $dashboard_project_data
     */
    protected function augmentWithAnnotators(array &$dashboard_project_data): void {
        $projectIds = array_column($dashboard_project_data, 'id');
        $counts = UserRelation::query()
            ->whereIn('project_id', $projectIds)
            ->where('relation_type', UserRelationsEnum::ANNOTATOR_OF_MANAGER)
            ->selectRaw('project_id, COUNT(*) as count')
            ->groupBy('project_id')
            ->pluck('count', 'project_id');

        foreach ($dashboard_project_data as &$project) {
            $project['annotators_count'] = (int) ($counts->get((int) $project['id']) ?? 0);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $dashboard_project_data
     */
    protected function augmentWithAnnotationTasks(array &$dashboard_project_data): void {
        $taskIds = array_column($dashboard_project_data, 'annotation_task_id');
        $tasks = AnnotationTask::query()->whereIn('id', $taskIds)->get()->keyBy('id');

        $datasetIds = array_column($dashboard_project_data, 'dataset_id');
        $datasets = Dataset::query()->whereIn('id', $datasetIds)->get()->keyBy('id');

        foreach ($dashboard_project_data as &$project) {
            $task = $tasks->get((int) $project['annotation_task_id']);
            if ($task instanceof AnnotationTask) {
                $project['annotation_task_title'] = $task->title;
            }

            $dataset = $datasets->get((int) $project['dataset_id']);
            if ($dataset instanceof Dataset) {
                $project['dataset_name'] = $dataset->name;
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $dashboard_project_data
     */
    private function augmentProjectData(array &$dashboard_project_data): void {
        $this->augmentWithAnnotationTasks($dashboard_project_data);
        $this->augmentWithSubprojects($dashboard_project_data);
        $this->augmentWithAnnotators($dashboard_project_data);
        $this->augmentWithNotifications($dashboard_project_data);
        $this->augmentWithManagers($dashboard_project_data);
        $this->augmentWithProgress($dashboard_project_data);
    }
}
