<?php

declare(strict_types=1);

namespace App\Services\Annotator;

use App\Models\User;
use App\Queries\GetActiveAnnotatorsByIdsQuery;
use App\Queries\GetActiveAnnotatorsQuery;
use App\Queries\GetActiveSubProjectIdsQuery;
use App\Queries\GetAnnotatorActiveProjectCountsQuery;
use App\Queries\GetAnnotatorSubprojectCountsQuery;
use App\Services\User\UserService;

readonly class AnnotatorService {
    public function __construct(
        private UserService $userService,
        private GetActiveAnnotatorsQuery $activeAnnotatorsQuery,
        private GetActiveAnnotatorsByIdsQuery $activeAnnotatorsByIdsQuery,
        private GetAnnotatorActiveProjectCountsQuery $annotatorActiveProjectCountsQuery,
        private GetActiveSubProjectIdsQuery $activeSubProjectIdsQuery,
        private GetAnnotatorSubprojectCountsQuery $annotatorSubprojectCountsQuery,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllAnnotators(): array {
        $annotators = $this->activeAnnotatorsQuery->get()
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
        $annotators = $this->activeAnnotatorsByIdsQuery->get($ids)
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

        $workloads = $this->userService->getWorkloads(array_column($annotators, 'id'));
        $values = array_values($workloads);

        if ($values === []) {
            foreach ($annotators as &$annotator) {
                $annotator['workload'] = 0.5;
            }

            return;
        }

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
}
