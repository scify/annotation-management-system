<?php

declare(strict_types=1);

namespace App\Services\Annotator;

use App\Models\User;
use App\Queries\GetActiveSubProjectIdsQuery;
use App\Queries\GetAnnotatorsQuery;
use App\Queries\GetCountsOfActiveProjectsPerAnnotatorQuery;
use App\Queries\GetCountsOfSubprojectsPerAnnotatorQuery;

readonly class AnnotatorService {
    public function __construct(
        private WorkloadService $workloadService,
        private GetAnnotatorsQuery $activeAnnotatorsQuery,
        private GetCountsOfActiveProjectsPerAnnotatorQuery $annotatorActiveProjectCountsQuery,
        private GetActiveSubProjectIdsQuery $activeSubProjectIdsQuery,
        private GetCountsOfSubprojectsPerAnnotatorQuery $annotatorSubprojectCountsQuery,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllAnnotators(): array {
        $annotators = $this->activeAnnotatorsQuery->getActive()
            ->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])
            ->values()
            ->all();

        $this->augmentAnnotatorData($annotators);

        return $annotators;
    }

    /**
     * @param  array<int, mixed>  $ids
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAnnotatorsByIds(array $ids): array {
        $annotators = $this->activeAnnotatorsQuery->getActive($ids)
            ->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])
            ->values()
            ->all();

        $this->augmentAnnotatorData($annotators);

        return $annotators;
    }

    /**
     * @param  array<int, array<string, mixed>>  $annotators
     */
    private function augmentAnnotatorData(array &$annotators): void {
        $this->augmentAnnotatorsWithProgress($annotators);
        $this->augmentAnnotatorsWithActiveProjects($annotators);
        $this->augmentAnnotatorsWithWorkload($annotators);
    }

    /**
     * @param  array<int, array<string, mixed>>  $annotators
     */
    private function augmentAnnotatorsWithProgress(array &$annotators): void {
        foreach ($annotators as &$annotator) {
            $annotator['annotator_progress'] = 0.5;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $annotators
     */
    private function augmentAnnotatorsWithActiveProjects(array &$annotators): void {
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
