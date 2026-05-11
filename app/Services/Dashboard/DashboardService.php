<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\RolesEnum;
use App\Enums\UserRelationsEnum;
use App\Models\Project;
use App\Models\User;
use App\Models\UserRelation;
use App\Queries\GetActiveAnnotatorsByIdsQuery;
use App\Queries\GetActiveAnnotatorsQuery;
use App\Queries\GetActiveSubProjectIdsQuery;
use App\Queries\GetAnnotatorActiveProjectCountsQuery;
use App\Queries\GetAnnotatorIdsByProjectsQuery;
use App\Queries\GetAnnotatorSubprojectCountsQuery;
use App\Queries\GetInProgressProjectsQuery;
use App\Queries\GetUserInProgressProjectsQuery;
use App\Services\User\UserService;

readonly class DashboardService {
    public function __construct(
        private UserService $userService,
        private GetInProgressProjectsQuery $inProgressProjectsQuery,
        private GetUserInProgressProjectsQuery $userInProgressProjectsQuery,
        private GetActiveAnnotatorsQuery $activeAnnotatorsQuery,
        private GetActiveAnnotatorsByIdsQuery $activeAnnotatorsByIdsQuery,
        private GetAnnotatorIdsByProjectsQuery $annotatorIdsByProjectsQuery,
        private GetAnnotatorActiveProjectCountsQuery $annotatorActiveProjectCountsQuery,
        private GetActiveSubProjectIdsQuery $activeSubProjectIdsQuery,
        private GetAnnotatorSubprojectCountsQuery $annotatorSubprojectCountsQuery,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getDataForDashboard(User $user): array {
        if ($user->hasRole(RolesEnum::ADMIN->value)) {
            $all_projects = $this->getAllInProgressProjects();
            $all_annotators = $this->getAllAnnotators();
            $my_projects = $this->getMyInProgressProjects($user->id, $all_projects);

            return [
                'platform_stats' => $this->getPlatformStats(),
                'all_projects' => $all_projects,
                'all_annotators' => $all_annotators,
                'my_projects' => $my_projects,
                'my_annotators' => $this->getMyAnnotators($my_projects, $all_annotators),
            ];
        }

        $my_projects = $this->getMyInProgressProjects($user->id);

        return [
            'my_projects' => $my_projects,
            'my_annotators' => $this->getMyAnnotators($my_projects),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $dashboard_project_data
     */
    protected function augmentProjectsWithManagers(array &$dashboard_project_data): void {
        foreach ($dashboard_project_data as &$project) {
            $project['owner_name'] = $project['owner']['username'] ?? null;
            $project['co_managers'] = array_values(array_filter(
                array_map(
                    fn (array $relation): ?array => isset($relation['user'])
                        ? ['id' => $relation['user']['id'], 'username' => $relation['user']['username']]
                        : null,
                    $project['co_manager_relations'] ?? []
                )
            ));
            unset($project['owner'], $project['co_manager_relations']);
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
    protected function augmentProjectsWithAnnotationTasks(array &$dashboard_project_data): void {
        foreach ($dashboard_project_data as &$project) {
            $project['annotation_task_title'] = $project['annotation_task']['title'] ?? null;
            $project['dataset_name'] = $project['dataset']['name'] ?? null;
            unset($project['annotation_task'], $project['dataset']);
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

        $counts = $this->annotatorActiveProjectCountsQuery->get($annotatorIds);
        $activeSubProjectIds = $this->activeSubProjectIdsQuery->get();
        $subProjectCounts = $this->annotatorSubprojectCountsQuery->get($annotatorIds, $activeSubProjectIds);

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

        $workloads = $this->userService->getWorkloads(array_column($annotators, 'id'));
        $values = array_values($workloads);
        $min = min($values);
        $max = max($values);
        $range = $max - $min;

        foreach ($annotators as &$annotator) {
            $raw = $workloads[(int) $annotator['id']] ?? 0;
            $annotator['workload'] = $range > 0
                ? round(0.1 + (($raw - $min) / $range) * 0.8, 2)
                : 0.5;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getAllInProgressProjects(): array {
        $dashboard_project_data = $this->inProgressProjectsQuery->get()
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
    private function getMyInProgressProjects(int $userId, ?array $my_projects = null): array {
        if ($my_projects === null) {
            $dashboard_project_data = $this->userInProgressProjectsQuery->get($userId)
                ->map(fn (Project $project) => $project->makeHidden(['is_delayed_to_start', 'is_delayed_to_end'])->toArray())
                ->values()
                ->all();

            $this->augmentProjectData($dashboard_project_data);
        } else {
            $collaboratorOwnerIds = UserRelation::query()
                ->where('related_user_id', $userId)
                ->where('relation_type', UserRelationsEnum::COLLABORATOR_OF_USER)
                ->pluck('user_id')
                ->all();

            $dashboard_project_data = array_values(array_filter(
                $my_projects,
                fn (array $project): bool => (int) $project['owner_user_id'] === $userId
                    || in_array((int) $project['owner_user_id'], $collaboratorOwnerIds, true)
            ));
        }

        return $dashboard_project_data;
    }

    /**
     * @return array{all_projects: int, all_annotators: int, all_managers: int, all_admins: int}
     */
    private function getPlatformStats(): array {
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
    private function getAllAnnotators(): array {
        $annotators = $this->activeAnnotatorsQuery->get()
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
    private function getMyAnnotators(array $my_projects, ?array $all_annotators = null): array {
        $projectIds = array_column($my_projects, 'id');
        if ($projectIds === []) {
            return [];
        }

        $annotatorIds = $this->annotatorIdsByProjectsQuery->get($projectIds);

        if ($annotatorIds === []) {
            return [];
        }

        if ($all_annotators === null) {
            $annotators = $this->activeAnnotatorsByIdsQuery->get($annotatorIds)
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
    private function augmentProjectData(array &$dashboard_project_data): void {
        $this->augmentProjectsWithAnnotationTasks($dashboard_project_data);
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
