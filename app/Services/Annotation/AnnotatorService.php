<?php

declare(strict_types=1);

namespace App\Services\Annotation;

use App\Models\AnnotatorOfProject;
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
     * @param  Collection<int, int>|null  $activeSubProjectIds
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
     * Returns annotator data for a specific project's show page.
     * Fetches annotators of any status and augments with counts, workload, and progress.
     *
     * @param  array<int, int>  $annotatorIds
     * @param  Collection<int, int>  $activeSubProjectIds  In-progress subproject IDs for this project
     * @param  array<int, array{progress: float, assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>}>  $progressBySubProject
     *
     * @return array<int, array<string, mixed>>
     */
    public function getProjectAnnotatorsData(array $annotatorIds, Collection $activeSubProjectIds, array $progressBySubProject = []): array {
        if ($annotatorIds === []) {
            return [];
        }

        $annotators = $this->activeAnnotatorsQuery->getAll($annotatorIds)
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'username' => $user->username,
                'status' => $user->status->value,
            ])
            ->values()
            ->all();

        $this->augmentAnnotatorsWithActiveProjects($annotators, $activeSubProjectIds);
        $this->augmentAnnotatorsWithProgress($annotators, $progressBySubProject);
        $this->augmentAnnotatorsWithWorkload($annotators);

        return $annotators;
    }

    public function toggleCanFlag(int $annotatorId, int $projectId): void {
        $record = AnnotatorOfProject::query()->where('user_id', $annotatorId)
            ->where('project_id', $projectId)
            ->firstOrFail();

        $record->can_flag = ! $record->can_flag;
        $record->save();
    }

    /**
     * @param  array<int, mixed>  $ids
     * @param  array<int, array{progress: float, assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>}>  $progressBySubProject
     * @param  Collection<int, int>|null  $activeSubProjectIds
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
     * @param  Collection<int, int>|null  $activeSubProjectIds
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
            /** @var int $uid */
            $uid = $annotator['id'];
            $done = $userTotals[$uid]['done'] ?? 0;
            $all = $userTotals[$uid]['all'] ?? 0;
            $annotator['annotator_progress'] = $all > 0 ? $done / $all : 0.0;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $annotators
     * @param  Collection<int, int>  $activeSubProjectIds
     */
    private function augmentAnnotatorsWithActiveProjects(array &$annotators, Collection $activeSubProjectIds): void {
        $annotatorIds = array_column($annotators, 'id');

        $counts = $this->annotatorActiveProjectCountsQuery->get($annotatorIds);
        $subProjectCounts = $this->annotatorSubprojectCountsQuery->get($annotatorIds, $activeSubProjectIds);

        foreach ($annotators as &$annotator) {
            /** @var int $annotatorId */
            $annotatorId = $annotator['id'];
            $annotator['active_projects_count'] = (int) ($counts->get($annotatorId) ?? 0);
            $annotator['active_subprojects_count'] = (int) ($subProjectCounts->get($annotatorId) ?? 0);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $annotators
     */
    private function augmentAnnotatorsWithWorkload(array &$annotators): void {
        if ($annotators === []) {
            return;
        }

        /** @var array<int, int> $annotatorIds */
        $annotatorIds = array_column($annotators, 'id');
        $workloads = $this->workloadService->computeNormalizedWorkloads($annotatorIds);

        foreach ($annotators as &$annotator) {
            /** @var int $annotatorId */
            $annotatorId = $annotator['id'];
            $annotator['workload'] = $workloads[$annotatorId]['total'] ?? 0.5;
        }
    }
}
