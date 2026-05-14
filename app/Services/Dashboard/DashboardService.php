<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\RolesEnum;
use App\Models\Project;
use App\Models\User;
use App\Queries\GetAnnotatorIdsByProjectsQuery;
use App\Queries\GetInProgressProjectsQuery;
use App\Queries\GetPlatformStatsQuery;
use App\Queries\GetProjectIdsByManagerQuery;
use App\Queries\GetUserInProgressProjectsQuery;
use App\Services\Annotator\AnnotatorService;

readonly class DashboardService {
    public function __construct(
        private AnnotatorService $annotatorService,
        private GetInProgressProjectsQuery $inProgressProjectsQuery,
        private GetUserInProgressProjectsQuery $userInProgressProjectsQuery,
        private GetAnnotatorIdsByProjectsQuery $annotatorIdsByProjectsQuery,
        private GetPlatformStatsQuery $platformStatsQuery,
        private GetProjectIdsByManagerQuery $projectIdsByManagerQuery,
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
            $ownerId = (int) $project['owner_user_id'];
            $project['owner_name'] = $project['owner']['username'] ?? null;
            $project['co_managers'] = array_values(array_filter(
                array_map(
                    fn (array $relation): ?array => isset($relation['user']) && (int) $relation['user']['id'] !== $ownerId
                        ? ['id' => $relation['user']['id'], 'username' => $relation['user']['username']]
                        : null,
                    $project['project_managers'] ?? []
                )
            ));
            unset($project['owner'], $project['project_managers']);
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
            $myProjectIds = $this->projectIdsByManagerQuery->get($userId);

            $dashboard_project_data = array_values(array_filter(
                $my_projects,
                fn (array $project): bool => in_array((int) $project['id'], $myProjectIds, true)
            ));
        }

        return $dashboard_project_data;
    }

    /**
     * @return array{all_projects: int, all_annotators: int, all_managers: int, all_admins: int}
     */
    private function getPlatformStats(): array {
        return $this->platformStatsQuery->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getAllAnnotators(): array {
        return $this->annotatorService->getAllAnnotators();
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
            return $this->annotatorService->getAnnotatorsByIds($annotatorIds);
        }

        $annotatorIds = array_map(fn (mixed $id): int => (int) $id, $annotatorIds);

        return array_values(array_filter(
            $all_annotators,
            fn (array $annotator): bool => in_array((int) $annotator['id'], $annotatorIds, true)
        ));
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
}
