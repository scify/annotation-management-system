<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Enums\UserRelationsEnum;
use App\Models\AnnotationTask;
use App\Models\Dataset;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use App\Models\UserRelation;
use App\Services\User\UserService;
use Illuminate\Database\Eloquent\Builder;

class DashboardService {
    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllInProgressProjects(): array {
        $dashboard_project_data = Project::query()
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->select(['id', 'name', 'owner_user_id', 'annotation_task_id', 'status', 'dataset_id', 'started_at', 'deadline_at'])
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
            ->select(['id', 'name', 'owner_user_id', 'annotation_task_id', 'dataset_id', 'status', 'started_at', 'deadline_at'])
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
    public function getAllAnnotators(): array {
        $annotators = User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $q) => $q->where('name', RolesEnum::ANNOTATOR->value))
            ->select(['id', 'name'])
            ->get()
            ->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])
            ->values()
            ->all();

        $this->augmentAnnotatorData($annotators);

        return $annotators;
    }

    /**
     * @param  array<int, array<string, mixed>>  $my_projects
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMyAnnotators(array $my_projects): array {
        $projectIds = array_column($my_projects, 'id');
        if ($projectIds === []) {
            return [];
        }

        $annotatorIds = UserRelation::query()
            ->whereIn('project_id', $projectIds)
            ->where('relation_type', UserRelationsEnum::ANNOTATOR_OF_MANAGER)
            ->pluck('user_id')
            ->unique()
            ->all();

        if (empty($annotatorIds)) {
            return [];
        }

        $annotators = User::query()
            ->where('is_active', true)
            ->whereIn('id', $annotatorIds)
            ->select(['id', 'name'])
            ->get()
            ->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])
            ->values()
            ->all();

        $this->augmentAnnotatorData($annotators);

        return $annotators;
    }

    /**
     * @param  array<int, array<string, mixed>>  $dashboard_project_data
     */
    protected function augmentProjectsWithManagers(array &$dashboard_project_data): void {
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
    protected function augmentProjectsWithProgress(array &$dashboard_project_data): void {
        foreach ($dashboard_project_data as &$project) {
            $project['project_progress'] = 0.5;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $dashboard_project_data
     */
    protected function augmentProjectsWithNotifications(array &$dashboard_project_data): void {
        foreach ($dashboard_project_data as &$project) {
            $project['notifications_count'] = 0;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $dashboard_project_data
     */
    protected function augmentProjectsWithSubprojects(array &$dashboard_project_data): void {
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
    protected function augmentProjectsWithAnnotators(array &$dashboard_project_data): void {
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
    protected function augmentProjectsWithAnnotationTasks(array &$dashboard_project_data): void {
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
    protected function augmentProjectsWithDateRange(array &$dashboard_project_data): void {
        foreach ($dashboard_project_data as &$project) {
            $project['date_range_start'] = $project['started_at'] ?? null;
            $project['date_range_end'] = $project['deadline_at'] ?? null;
            unset($project['started_at'], $project['deadline_at']);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $annotators
     */
    protected function augmentAnnotatorsWithProgress(array &$annotators): void {
        foreach ($annotators as &$annotator) {
            $annotator['annotator_progress'] = 0.5;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $annotators
     */
    protected function augmentAnnotatorsWithActiveProjects(array &$annotators): void {
        $annotatorIds = array_column($annotators, 'id');

        $counts = UserRelation::query()
            ->whereIn('user_relations.user_id', $annotatorIds)
            ->where('user_relations.relation_type', UserRelationsEnum::ANNOTATOR_OF_MANAGER)
            ->join('projects', 'projects.id', '=', 'user_relations.project_id')
            ->where('projects.status', ProjectStatusEnum::IN_PROGRESS)
            ->selectRaw('user_relations.user_id, COUNT(*) as count')
            ->groupBy('user_relations.user_id')
            ->pluck('count', 'user_relations.user_id');

        foreach ($annotators as &$annotator) {
            $annotator['active_projects_count'] = (int) ($counts->get((int) $annotator['id']) ?? 0);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $annotators
     */
    protected function augmentAnnotatorsWithWorkload(array &$annotators): void {
        if ($annotators === []) {
            return;
        }

        $userModels = User::query()
            ->whereIn('id', array_column($annotators, 'id'))
            ->get()
            ->keyBy('id');

        $workloads = [];
        foreach ($annotators as $annotator) {
            $user = $userModels->get((int) $annotator['id']);
            $workloads[(int) $annotator['id']] = $user instanceof User
                ? (float) $this->userService->getWorkload($user)
                : 0.0;
        }

        $values = array_values($workloads);
        $min = min($values);
        $max = max($values);
        $range = $max - $min;

        foreach ($annotators as &$annotator) {
            $raw = $workloads[(int) $annotator['id']];
            $annotator['workload'] = $range > 0
                ? round(0.1 + (($raw - $min) / $range) * 0.8, 2)
                : 0.5;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $dashboard_project_data
     */
    private function augmentProjectData(array &$dashboard_project_data): void {
        $this->augmentProjectsWithAnnotationTasks($dashboard_project_data);
        $this->augmentProjectsWithSubprojects($dashboard_project_data);
        $this->augmentProjectsWithAnnotators($dashboard_project_data);
        $this->augmentProjectsWithNotifications($dashboard_project_data);
        $this->augmentProjectsWithManagers($dashboard_project_data);
        $this->augmentProjectsWithProgress($dashboard_project_data);
        $this->augmentProjectsWithDateRange($dashboard_project_data);
    }

    /**
     * @param  array<int, array<string, mixed>>  $annotators
     */
    private function augmentAnnotatorData(array &$annotators): void {
        $this->augmentAnnotatorsWithProgress($annotators);
        $this->augmentAnnotatorsWithActiveProjects($annotators);
        $this->augmentAnnotatorsWithWorkload($annotators);
    }
}
