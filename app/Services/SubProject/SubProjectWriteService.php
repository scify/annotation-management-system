<?php

declare(strict_types=1);

namespace App\Services\SubProject;

use App\Enums\ProjectStatusEnum;
use App\Exceptions\AnnotatorDetachException;
use App\Exceptions\SubProjectStatusException;
use App\Models\AnnotationAssignment;
use App\Models\SubProject;
use App\Queries\Project\UpdateProjectStatusQuery;
use App\Queries\SubProject\AttachAnnotatorsToSubProjectQuery;
use App\Queries\SubProject\DeleteAnnotationsBySubProjectQuery;
use App\Queries\SubProject\DetachAnnotatorFromSubProjectQuery;
use App\Queries\SubProject\GetAssignmentsBySubProjectsQuery;
use App\Queries\SubProject\GetPendingSubProjectsRangeQuery;
use App\Queries\SubProject\GetProgressQuery;
use App\Queries\SubProject\GetSubProjectByIdQuery;
use App\Queries\SubProject\StoreSubProjectQuery;
use App\Queries\SubProject\UpdateSubProjectStatusQuery;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

readonly class SubProjectWriteService {
    public function __construct(
        private AttachAnnotatorsToSubProjectQuery $attachAnnotatorsToSubProjectQuery,
        private DeleteAnnotationsBySubProjectQuery $deleteAnnotationsBySubProjectQuery,
        private DetachAnnotatorFromSubProjectQuery $detachAnnotatorFromSubProjectQuery,
        private GetAssignmentsBySubProjectsQuery $assignmentsBySubProjectsQuery,
        private GetPendingSubProjectsRangeQuery $pendingSubProjectsRangeQuery,
        private GetProgressQuery $progressQuery,
        private GetSubProjectByIdQuery $subProjectByIdQuery,
        private StoreSubProjectQuery $storeSubProjectQuery,
        private UpdateSubProjectStatusQuery $updateSubProjectStatusQuery,
        private UpdateProjectStatusQuery $updateProjectStatusQuery,
    ) {}

    public function changeStatus(SubProject $subProject, ProjectStatusEnum $newStatus): SubProject {
        $parentStatus = $subProject->project->status;

        // Completing a subproject requires the parent to be in_progress
        if ($newStatus === ProjectStatusEnum::COMPLETED && $parentStatus !== ProjectStatusEnum::IN_PROGRESS) {
            throw SubProjectStatusException::projectNotInProgress();
        }

        // Moving to in_progress is allowed when parent is pending or in_progress;
        // any other parent state (completed) blocks the transition
        if ($newStatus === ProjectStatusEnum::IN_PROGRESS && $parentStatus === ProjectStatusEnum::COMPLETED) {
            throw SubProjectStatusException::projectNotInProgress();
        }

        $allowed = match ($subProject->status) {
            ProjectStatusEnum::PENDING => ProjectStatusEnum::IN_PROGRESS,
            ProjectStatusEnum::IN_PROGRESS => ProjectStatusEnum::COMPLETED,
            ProjectStatusEnum::COMPLETED => null,
        };

        if ($allowed !== $newStatus) {
            throw SubProjectStatusException::invalidTransition($subProject->status, $newStatus);
        }

        $this->updateSubProjectStatusQuery->execute($subProject, $newStatus);

        // Auto-promote parent project from pending to in_progress
        if ($newStatus === ProjectStatusEnum::IN_PROGRESS && $parentStatus === ProjectStatusEnum::PENDING) {
            $this->updateProjectStatusQuery->execute($subProject->project, ProjectStatusEnum::IN_PROGRESS);
        }

        return $subProject;
    }

    /**
     * @param  array<int, int>  $annotatorIds
     */
    public function attachAnnotators(int $subProjectId, array $annotatorIds): void {
        $subProject = $this->subProjectByIdQuery->getWithProject($subProjectId);
        $this->attachAnnotatorsToSubProjectQuery->attach($subProject, $annotatorIds);
    }

    public function detachAnnotator(int $subProjectId, int $annotatorId): void {
        $subProject = $this->subProjectByIdQuery->get($subProjectId);

        if ($subProject->status !== ProjectStatusEnum::PENDING) {
            throw AnnotatorDetachException::subProjectNotPending();
        }

        $this->detachAnnotatorFromSubProjectQuery->detach($subProjectId, $annotatorId);
    }

    public function deleteSubProject(SubProject $subProject): void {
        $this->deleteAnnotationsBySubProjectQuery->execute($subProject->id);
        $subProject->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function storeSubProject(int $projectId, array $data): void {
        $this->storeSubProjectQuery->execute($projectId, $data);
    }

    /**
     * @param  array<string, mixed>  $data  Validated data from SubProjectUpdateRequest:
     *                                      name, priority, is_flexible, requires_confirmation,
     *                                      minimum_annotations, scheduled_at, deadline_at,
     *                                      from_instance, to_instance
     */
    public function updateSubProject(SubProject $subProject, array $data): SubProject {
        // TODO: implement — map validated fields to model columns and persist:
        //   name                  → name
        //   priority              → priority
        //   is_flexible           → flexible
        //   is_flexible + requires_confirmation → auto_submission (see StoreSubProjectQuery for the derivation logic)
        //   minimum_annotations   → minimum_annotators
        //   from_instance         → first_instance_index
        //   to_instance           → last_instance_index
        //   scheduled_at          → scheduled_at
        //   deadline_at           → deadline_at
        return $subProject;
    }

    /**
     * @param  array<int, int>  $subProjectIds
     *
     * @return array<int, array{
     *     progress: float,
     *     assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>
     * }>
     */
    public function getProgress(array $subProjectIds): array {
        if ($subProjectIds === []) {
            return [];
        }

        /** @var array<int, array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>> $assignmentsBySubProject */
        $assignmentsBySubProject = array_fill_keys($subProjectIds, []);

        /** @var Collection<int, AnnotationAssignment> $assignmentRows */
        $assignmentRows = $this->assignmentsBySubProjectsQuery->get($subProjectIds);

        $assignmentToSubProject = [];
        $assignmentUsers = [];

        foreach ($assignmentRows as $row) {
            $assignmentToSubProject[$row->id] = $row->sub_project_id;
            $assignmentUsers[$row->id] = $row->user_id;
        }

        $allIds = array_keys($assignmentToSubProject);

        /** @var array<int, array{annotations_all: int, annotations_done: int, progress: float}> $progressData */
        $progressData = $allIds !== [] ? $this->progressQuery->get($allIds) : [];

        // For assignments with no Annotation records, check whether the subproject is PENDING.
        // If so, synthesise planned progress using the subproject's instance range so that
        // pending work is included in the annotator's progress denominator.
        $assignmentIdsWithNoData = array_diff($allIds, array_keys($progressData));

        if ($assignmentIdsWithNoData !== []) {
            /** @var array<int, int> $spIdsForMissing */
            $spIdsForMissing = array_values(array_unique(
                array_intersect_key($assignmentToSubProject, array_flip($assignmentIdsWithNoData))
            ));

            /** @var \Illuminate\Support\Collection<int, SubProject> $pendingMeta */
            $pendingMeta = $this->pendingSubProjectsRangeQuery->get($spIdsForMissing)->keyBy('id');

            foreach ($assignmentIdsWithNoData as $assignmentId) {
                $spId = $assignmentToSubProject[$assignmentId];
                $sp = $pendingMeta->get($spId);
                if ($sp === null) {
                    continue;
                }

                $progressData[$assignmentId] = [
                    'annotations_all' => $sp->last_instance_index - $sp->first_instance_index + 1,
                    'annotations_done' => 0,
                    'progress' => 0.0,
                ];
            }
        }

        foreach ($progressData as $assignmentId => $data) {
            $spId = $assignmentToSubProject[$assignmentId];
            $assignmentsBySubProject[$spId][$assignmentId] = [...$data, 'user_id' => $assignmentUsers[$assignmentId]];
        }

        $result = [];

        foreach ($subProjectIds as $spId) {
            $assignments = $assignmentsBySubProject[$spId];
            $totalAll = array_sum(array_column($assignments, 'annotations_all'));
            $totalDone = array_sum(array_column($assignments, 'annotations_done'));
            $result[$spId] = [
                'progress' => $totalAll > 0 ? (float) ($totalDone / $totalAll) : 0.0,
                'assignments' => $assignments,
            ];
        }

        return $result;
    }

    /**
     * @param  Collection<int, SubProject>  $subProjects
     *
     * @return array<int, array{id: int, name: string, status: string, scheduled_at: Carbon|null, deadline_at: Carbon|null, started_at: Carbon|null, completed_at: Carbon|null, progress: float, annotators_count: int, first_instance_index: int, last_instance_index: int}>
     */
    public function getSubProjectsData(Collection $subProjects): array {
        /** @var array<int, int> $subProjectIds */
        $subProjectIds = $subProjects->pluck('id')->all();
        $progressBySubProject = $this->getProgress($subProjectIds);
        $notificationCounts = $this->getNotificationCounts($subProjectIds);

        return $subProjects->map(function (SubProject $subProject) use ($progressBySubProject, $notificationCounts): array {
            $spProgress = $progressBySubProject[$subProject->id] ?? ['progress' => 0.0, 'assignments' => []];

            return [
                'id' => $subProject->id,
                'name' => $subProject->name,
                'status' => $subProject->status->value,
                'priority' => $subProject->priority->value,
                'scheduled_at' => $subProject->scheduled_at,
                'deadline_at' => $subProject->deadline_at,
                'started_at' => $subProject->started_at,
                'completed_at' => $subProject->completed_at,
                'progress' => $spProgress['progress'],
                'annotators_count' => count($spProgress['assignments']),
                'first_instance_index' => $subProject->first_instance_index,
                'last_instance_index' => $subProject->last_instance_index,
                'notification_count' => $notificationCounts[$subProject->id] ?? 0,
            ];
        })->values()->all();
    }

    /**
     * TODO: implement once notifications are available.
     *
     * @param  array<int, int>  $subProjectIds
     *
     * @return array<int, int>
     */
    private function getNotificationCounts(array $subProjectIds): array {
        return array_fill_keys($subProjectIds, 0);
    }
}
