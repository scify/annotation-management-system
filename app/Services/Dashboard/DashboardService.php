<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Queries\GetAnnotatorIdsByProjectsQuery;
use App\Queries\GetPlatformStatsQuery;
use App\Services\Annotator\AnnotatorService;
use App\Services\Project\ProjectService;

readonly class DashboardService {
    public function __construct(
        private AnnotatorService $annotatorService,
        private GetAnnotatorIdsByProjectsQuery $annotatorIdsByProjectsQuery,
        private GetPlatformStatsQuery $platformStatsQuery,
        private ProjectService $projectService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getDataForDashboard(User $user): array {
        if ($user->hasRole(RolesEnum::ADMIN->value)) {
            $all_projects = $this->projectService->getAllInProgressProjects();
            $all_annotators = $this->getAllAnnotators();
            $my_projects = $this->projectService->getMyInProgressProjects($user->id, $all_projects);

            return [
                'platform_stats' => $this->getPlatformStats(),
                'all_projects' => $all_projects,
                'all_annotators' => $all_annotators,
                'my_projects' => $my_projects,
                'my_annotators' => $this->getMyAnnotators($my_projects, $all_annotators),
            ];
        }

        $my_projects = $this->projectService->getMyInProgressProjects($user->id);

        return [
            'my_projects' => $my_projects,
            'my_annotators' => $this->getMyAnnotators($my_projects),
        ];
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
            fn (array $annotator): bool => in_array((int) $annotator['id'], $annotatorIds, true),
        ));
    }
}
