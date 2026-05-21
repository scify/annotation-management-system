<?php

declare(strict_types=1);

namespace App\Services\Annotator;

use App\Models\User;
use App\Queries\GetAnnotatorsQuery;
use App\Queries\GetCountsOfActiveProjectsPerAnnotatorQuery;
use App\Queries\GetCountsOfSubprojectsPerAnnotatorQuery;
use Illuminate\Support\Collection;

readonly class AnnotatorService {
    public function __construct(
        private WorkloadService $workloadService,
        private GetAnnotatorsQuery $activeAnnotatorsQuery,
        private GetCountsOfActiveProjectsPerAnnotatorQuery $annotatorActiveProjectCountsQuery,
        private GetCountsOfSubprojectsPerAnnotatorQuery $annotatorSubprojectCountsQuery,
    ) {}

    /**
     * @param  array<int, array{progress: float, assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>}>  $progressBySubProject
     * @param  Collection<int, mixed>|null  $activeSubProjectIds
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllAnnotators(array $progressBySubProject = [], ?Collection $activeSubProjectIds = null): array {
        $annotators = $this->activeAnnotatorsQuery->getActive()
            ->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])
            ->values()
            ->all();

        $this->augmentAnnotatorData($annotators, $progressBySubProject, $activeSubProjectIds);

        return $annotators;
    }

    /**
     * @param  array<int, mixed>  $ids
     * @param  array<int, array{progress: float, assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>}>  $progressBySubProject
     * @param  Collection<int, mixed>|null  $activeSubProjectIds
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAnnotatorsByIds(array $ids, array $progressBySubProject = [], ?Collection $activeSubProjectIds = null): array {
        $annotators = $this->activeAnnotatorsQuery->getActive($ids)
            ->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])
            ->values()
            ->all();

        $this->augmentAnnotatorData($annotators, $progressBySubProject, $activeSubProjectIds);

        return $annotators;
    }

    /**
     * @param  array<int, array<string, mixed>>  $annotators
     * @param  array<int, array{progress: float, assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>}>  $progressBySubProject
     * @param  Collection<int, mixed>|null  $activeSubProjectIds
     */
    private function augmentAnnotatorData(array &$annotators, array $progressBySubProject, ?Collection $activeSubProjectIds = null): void {
        $this->augmentAnnotatorsWithProgress($annotators, $progressBySubProject);
        $this->augmentAnnotatorsWithActiveProjects($annotators, $activeSubProjectIds ?? collect());
        $this->augmentAnnotatorsWithWorkload($annotators);
    }

    /**
     * @param  array<int, array<string, mixed>>  $annotators
     * @param  array<int, array{progress: float, assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>}>  $progressBySubProject
     */
    private function augmentAnnotatorsWithProgress(array &$annotators, array $progressBySubProject): void {
        $userTotals = [];

        foreach ($progressBySubProject as $spProgress) {
            foreach ($spProgress['assignments'] as $assignment) {
                $uid = $assignment['user_id'];
                $userTotals[$uid]['done'] = ($userTotals[$uid]['done'] ?? 0) + $assignment['annotations_done'];
                $userTotals[$uid]['all'] = ($userTotals[$uid]['all'] ?? 0) + $assignment['annotations_all'];
            }
        }

        foreach ($annotators as &$annotator) {
            $uid = (int) $annotator['id'];
            $done = $userTotals[$uid]['done'] ?? 0;
            $all = $userTotals[$uid]['all'] ?? 0;
            $annotator['annotator_progress'] = $all > 0 ? $done / $all : 0.0;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $annotators
     * @param  Collection<int, mixed>  $activeSubProjectIds
     */
    private function augmentAnnotatorsWithActiveProjects(array &$annotators, Collection $activeSubProjectIds): void {
        $annotatorIds = array_column($annotators, 'id');

        $counts = $this->annotatorActiveProjectCountsQuery->get($annotatorIds);
        $subProjectCounts = $this->annotatorSubprojectCountsQuery->get($annotatorIds, $activeSubProjectIds);

        foreach ($annotators as &$annotator) {
            $annotator['active_projects_count'] = (int) ($counts->get((int) $annotator['id']) ?? 0);
            $annotator['active_subprojects_count'] = (int) ($subProjectCounts->get((int) $annotator['id']) ?? 0);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $annotators
     */
    private function augmentAnnotatorsWithWorkload(array &$annotators): void {
        if ($annotators === []) {
            return;
        }

        $workloads = $this->workloadService->computeNormalizedWorkloads(array_column($annotators, 'id'));

        foreach ($annotators as &$annotator) {
            $annotator['workload'] = $workloads[(int) $annotator['id']]['total'] ?? 0.5;
        }
    }
}
