<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\User;
use App\Queries\GetAnnotatorIdsByProjectsQuery;
use App\Queries\GetPlatformStatsQuery;
use App\Queries\GetProjectIdsManagedByUserQuery;
use App\Queries\GetProjectsQuery;
use App\Queries\GetSubProjectIdsQuery;
use App\Services\Annotator\AnnotatorService;
use App\Services\Project\ProjectService;
use App\Services\Project\SubProjectService;
use Illuminate\Support\Collection;

readonly class DashboardService {
    public function __construct(
        private AnnotatorService $annotatorService,
        private GetAnnotatorIdsByProjectsQuery $annotatorIdsByProjectsQuery,
        private GetPlatformStatsQuery $platformStatsQuery,
        private GetProjectIdsManagedByUserQuery $projectIdsManagedByUserQuery,
        private GetProjectsQuery $projectsQuery,
        private GetSubProjectIdsQuery $subProjectIdsQuery,
        private ProjectService $projectService,
        private SubProjectService $subProjectService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getDataForDashboard(User $user): array {
        if ($user->hasRole(RolesEnum::ADMIN->value)) {
            $projectIds = $this->projectsQuery->getIds(ProjectStatusEnum::IN_PROGRESS);
            $activeSubProjectIds = $this->subProjectIdsQuery->get($projectIds, ProjectStatusEnum::IN_PROGRESS);
            $progressBySubProject = $this->subProjectService->getProgress($activeSubProjectIds->all());

            $all_projects = $this->projectService->getAllInProgressProjects();
            $all_annotators = $this->getAllAnnotators($progressBySubProject, $activeSubProjectIds);
            $my_projects = $this->projectService->getMyInProgressProjects($user->id, $all_projects);

            return [
                'platform_stats' => $this->getPlatformStats(),
                'all_projects' => $all_projects,
                'all_annotators' => $all_annotators,
                'my_projects' => $my_projects,
                'my_annotators' => $this->getMyAnnotators($my_projects, $all_annotators, $progressBySubProject, $activeSubProjectIds),
            ];
        }

        $projectIds = $this->projectIdsManagedByUserQuery->get($user->id, ProjectStatusEnum::IN_PROGRESS);
        $activeSubProjectIds = $this->subProjectIdsQuery->get($projectIds, ProjectStatusEnum::IN_PROGRESS);
        $progressBySubProject = $this->subProjectService->getProgress($activeSubProjectIds->all());

        $my_projects = $this->projectService->getMyInProgressProjects($user->id);

        return [
            'my_projects' => $my_projects,
            'my_annotators' => $this->getMyAnnotators($my_projects, null, $progressBySubProject, $activeSubProjectIds),
        ];

    }

    /**
     * @return array{all_projects: int, all_annotators: int, all_managers: int, all_admins: int}
     */
    private function getPlatformStats(): array {
        return $this->platformStatsQuery->get();
    }

    /**
     * @param  array<int, array{progress: float, assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>}>  $progressBySubProject
     * @param  Collection<int, mixed>|null  $activeSubProjectIds
     *
     * @return array<int, array<string, mixed>>
     */
    private function getAllAnnotators(array $progressBySubProject = [], ?Collection $activeSubProjectIds = null): array {
        return $this->annotatorService->getAllAnnotators($progressBySubProject, $activeSubProjectIds);
    }

    /**
     * @param  array<int, array<string, mixed>>  $my_projects
     * @param  array<int, array<string, mixed>>|null  $all_annotators
     * @param  array<int, array{progress: float, assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>}>  $progressBySubProject
     * @param  Collection<int, mixed>|null  $activeSubProjectIds
     *
     * @return array<int, array<string, mixed>>
     */
    private function getMyAnnotators(array $my_projects, ?array $all_annotators = null, array $progressBySubProject = [], ?Collection $activeSubProjectIds = null): array {
        $projectIds = array_column($my_projects, 'id');
        if ($projectIds === []) {
            return [];
        }

        $annotatorIds = $this->annotatorIdsByProjectsQuery->get($projectIds);

        if ($annotatorIds === []) {
            return [];
        }

        if ($all_annotators === null) {
            return $this->annotatorService->getAnnotatorsByIds($annotatorIds, $progressBySubProject, $activeSubProjectIds);
        }

        $annotatorIds = array_map(fn (mixed $id): int => (int) $id, $annotatorIds);

        return array_values(array_filter(
            $all_annotators,
            fn (array $annotator): bool => in_array((int) $annotator['id'], $annotatorIds, true),
        ));
    }
}
