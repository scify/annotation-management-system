<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\SubProject;
use App\Models\User;
use App\Queries\Annotator\GetAnnotatorIdsByProjectsQuery;
use App\Queries\Dashboard\GetPlatformStatsQuery;
use App\Queries\Project\GetProjectIdsManagedByUserQuery;
use App\Queries\Project\GetProjectsQuery;
use App\Queries\Project\GetSubProjectIdsQuery;
use App\Queries\SubProject\GetAnnotationCountsBySubProjectsQuery;
use App\Queries\SubProject\GetSubProjectsForAnnotatorQuery;
use App\Services\Annotation\AnnotatorService;
use App\Services\Project\ProjectReadService;
use App\Services\SubProject\SubProjectWriteService;
use Illuminate\Support\Collection;

readonly class DashboardService {
    public function __construct(
        private AnnotatorService $annotatorService,
        private GetAnnotationCountsBySubProjectsQuery $annotationCountsBySubProjectsQuery,
        private GetAnnotatorIdsByProjectsQuery $annotatorIdsByProjectsQuery,
        private GetPlatformStatsQuery $platformStatsQuery,
        private GetProjectIdsManagedByUserQuery $projectIdsManagedByUserQuery,
        private GetProjectsQuery $projectsQuery,
        private GetSubProjectIdsQuery $subProjectIdsQuery,
        private GetSubProjectsForAnnotatorQuery $subProjectsForAnnotatorQuery,
        private ProjectReadService $projectReadService,
        private SubProjectWriteService $subProjectService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getDataForAnnotatorDashboard(User $user): array {
        $subprojectModels = $this->subProjectsForAnnotatorQuery->get($user);

        /** @var array<int, int> $subProjectIds */
        $subProjectIds = $subprojectModels->pluck('id')->all();
        $counts = $this->annotationCountsBySubProjectsQuery->get($subProjectIds);

        $subprojects = $subprojectModels
            ->map(function (SubProject $subProject) use ($counts): array {
                $c = $counts[$subProject->id] ?? ['pending_count' => 0, 'submitted_count' => 0, 'not_annotated_count' => 0];
                $total = $c['not_annotated_count'] + $c['pending_count'] + $c['submitted_count'];

                $entry = [
                    'id' => $subProject->id,
                    'name' => $subProject->name,
                    'project_name' => $subProject->project->name,
                    'flexible' => $subProject->flexible,
                    'auto_submission' => $subProject->auto_submission,
                    'scheduled_at' => $subProject->scheduled_at,
                    'deadline_at' => $subProject->deadline_at,
                    'started_at' => $subProject->started_at,
                    'completed_at' => $subProject->completed_at,
                    'submitted_count' => $c['submitted_count'],
                    'not_annotated_count' => $c['not_annotated_count'],
                    'submitted_pct' => $total > 0 ? round($c['submitted_count'] / $total * 100, 2) : 0.0,
                ];

                if (! $subProject->auto_submission) {
                    $entry['pending_count'] = $c['pending_count'];
                    $entry['submitted_and_pending_pct'] = $total > 0 ? round(($c['submitted_count'] + $c['pending_count']) / $total * 100, 2) : 0.0;
                }

                return $entry;
            })
            ->all();

        return [
            'subprojects' => $subprojects,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataForDashboard(User $user): array {
        if ($user->hasRole(RolesEnum::ADMIN->value)) {
            $projectIds = $this->projectsQuery->getIds(ProjectStatusEnum::IN_PROGRESS);
            $activeSubProjectIds = $this->subProjectIdsQuery->get($projectIds, ProjectStatusEnum::IN_PROGRESS);
            $progressBySubProject = $this->subProjectService->getProgress($activeSubProjectIds->all());

            $all_projects = $this->projectReadService->getAllInProgressProjects();
            $all_annotators = $this->getAllAnnotators($progressBySubProject, $activeSubProjectIds);
            $my_projects = $this->projectReadService->getMyInProgressProjects($user->id, $all_projects);

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

        $my_projects = $this->projectReadService->getMyInProgressProjects($user->id);

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
     * @param  Collection<int, int>|null  $activeSubProjectIds
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
     * @param  Collection<int, int>|null  $activeSubProjectIds
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

        $annotatorIds = array_map(fn (mixed $id): int => $id, $annotatorIds);

        return array_values(array_filter(
            $all_annotators,
            fn (array $annotator): bool => is_int($annotator['id']) && in_array($annotator['id'], $annotatorIds, true),
        ));
    }
}
