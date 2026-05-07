<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Enums\UserRelationsEnum;
use App\Models\AnnotationAssignment;
use App\Models\AnnotationTask;
use App\Models\Dataset;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use App\Models\UserRelation;
use App\Services\User\UserService;
use Illuminate\Database\Eloquent\Builder;

readonly class DashboardService {
    public function __construct(
        private UserService $userService,
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
     * @param  array<int, array<string, mixed>>|null  $my_projects
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMyInProgressProjects(int $userId, ?array $my_projects = null): array {
        $collaboratorOwnerIds = UserRelation::query()->where('related_user_id', $userId)
            ->where('relation_type', UserRelationsEnum::COLLABORATOR_OF_USER)
            ->select('user_id');

        if ($my_projects === null) {
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
        } else {
            $collaboratorOwnerIdsArray = $collaboratorOwnerIds->pluck('user_id')->all();
            $dashboard_project_data = array_values(array_filter(
                $my_projects,
                fn (array $project): bool => (int) $project['owner_user_id'] === $userId
                    || in_array((int) $project['owner_user_id'], $collaboratorOwnerIdsArray, true)
            ));
        }

        return $dashboard_project_data;
    }

    /**
     * @return array{all_projects: int, all_annotators: int, all_managers: int, all_admins: int}
     */
    public function getPlatformStats(): array {
        $allProjects = Project::query()->count();

        $activeUsers = User::query()
            ->where('is_active', true)
            ->with('roles')
            ->get();

        $allAnnotators = 0;
        $allManagers = 0;
        $allAdmins = 0;

        foreach ($activeUsers as $user) {
            $roleName = $user->getRoleNames()->first();
            if ($roleName === RolesEnum::ANNOTATOR->value) {
                $allAnnotators++;
            } elseif ($roleName === RolesEnum::ANNOTATION_MANAGER->value) {
                $allManagers++;
            } elseif ($roleName === RolesEnum::ADMIN->value) {
                $allAdmins++;
            }
        }

        return [
            'all_projects' => $allProjects,
            'all_annotators' => $allAnnotators,
            'all_managers' => $allManagers,
            'all_admins' => $allAdmins,
        ];
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
     * @param  array<int, array<string, mixed>>|null  $all_annotators
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMyAnnotators(array $my_projects, ?array $all_annotators = null): array {
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

        if ($all_annotators === null) {
            $annotators = User::query()
                ->where('is_active', true)
                ->whereIn('id', $annotatorIds)
                ->select(['id', 'name'])
                ->get()
                ->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])
                ->values()
                ->all();

            $this->augmentAnnotatorData($annotators);
        } else {
            $annotatorIds = array_map(fn (mixed $id): int => (int) $id, $annotatorIds);
            $annotators = array_values(array_filter(
                $all_annotators,
                fn (array $annotator): bool => in_array((int) $annotator['id'], $annotatorIds, true)
            ));
        }

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

        $activeSubProjectIds = SubProject::query()
            ->join('projects', 'projects.id', '=', 'sub_projects.project_id')
            ->where('projects.status', ProjectStatusEnum::IN_PROGRESS)
            ->where('sub_projects.status', ProjectStatusEnum::IN_PROGRESS)
            ->pluck('sub_projects.id');

        $subProjectCounts = AnnotationAssignment::query()
            ->whereIn('user_id', $annotatorIds)
            ->whereIn('sub_project_id', $activeSubProjectIds)
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->pluck('count', 'user_id');

        foreach ($annotators as &$annotator) {
            $annotator['active_projects_count'] = (int) ($counts->get((int) $annotator['id']) ?? 0);
            $annotator['active_subprojects_count'] = (int) ($subProjectCounts->get((int) $annotator['id']) ?? 0);
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
