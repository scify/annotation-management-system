<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Enums\AgreementEnum;
use App\Models\AnnotationAssignment;
use App\Models\AnnotatorOfProject;
use App\Models\SubProject;
use App\Queries\GetAnnotatorProjectLinksByProjectQuery;
use App\Queries\GetCountsOfFlagsQuery;
use App\Queries\GetProgressQuery;
use App\Queries\GetSubProjectIdsQuery;
use App\Queries\GetSubsetInfoByProjectQuery;
use App\Services\Annotation\AnnotationService;
use App\Services\Annotation\AnnotatorService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

readonly class SubProjectService {
    public function __construct(
        private AnnotationService $annotationService,
        private AnnotatorService $annotatorService,
        private GetAnnotatorProjectLinksByProjectQuery $annotatorProjectLinksQuery,
        private GetCountsOfFlagsQuery $flagsQuery,
        private GetProgressQuery $progressQuery,
        private GetSubProjectIdsQuery $subProjectIdsQuery,
        private GetSubsetInfoByProjectQuery $subsetInfoQuery,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * TODO: implement subproject store
     */
    public function storeSubProject(int $projectId, array $data): void {}

    /**
     * @return array<string, mixed>
     */
    public function getDataForEditSubProject(int $projectId, int $subprojectId): array {
        $subProject = SubProject::query()
            ->with(['project.annotationTask', 'project.dataset:id,name'])
            ->where('project_id', $projectId)
            ->findOrFail($subprojectId);

        return [
            'subproject_data' => $this->buildSubProjectData($subProject),
            'annotators_data' => $this->buildAnnotatorsData($subProject),
            'annotations_data' => $this->buildAnnotationsData($subProject),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataForCreateSubProject(int $projectId): array {
        return [
            'annotators_data' => $this->getAnnotatorsDataForCreate($projectId),
            'subset_data' => $this->getSubsetDataForCreate($projectId),
        ];
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
        $assignmentRows = AnnotationAssignment::query()
            ->whereIn('sub_project_id', $subProjectIds)
            ->select('id', 'sub_project_id', 'user_id')
            ->get();

        $assignmentToSubProject = [];
        $assignmentUsers = [];

        foreach ($assignmentRows as $row) {
            $assignmentToSubProject[$row->id] = $row->sub_project_id;
            $assignmentUsers[$row->id] = $row->user_id;
        }

        $allIds = array_keys($assignmentToSubProject);

        if ($allIds !== []) {
            foreach ($this->progressQuery->get($allIds) as $assignmentId => $data) {
                $spId = $assignmentToSubProject[$assignmentId];
                $assignmentsBySubProject[$spId][$assignmentId] = [...$data, 'user_id' => $assignmentUsers[$assignmentId]];
            }
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
     * @return array<string, mixed>
     */
    private function buildSubProjectData(SubProject $subProject): array {
        $progress = $this->getProgress([$subProject->id]);
        $spProgress = $progress[$subProject->id]['progress'] ?? 0.0;

        return [
            'id' => $subProject->id,
            'project_id' => $subProject->project_id,
            'name' => $subProject->name,
            'status' => $subProject->status->value,
            'priority' => $subProject->priority->value,
            'flexible' => $subProject->flexible,
            'auto_submission' => $subProject->auto_submission,
            'minimum_annotators' => $subProject->minimum_annotators,
            'first_instance_index' => $subProject->first_instance_index,
            'last_instance_index' => $subProject->last_instance_index,
            'scheduled_at' => $subProject->scheduled_at,
            'deadline_at' => $subProject->deadline_at,
            'started_at' => $subProject->started_at,
            'completed_at' => $subProject->completed_at,
            'dataset_id' => $subProject->project->dataset->id,
            'dataset_name' => $subProject->project->dataset->name,
            'progress' => $spProgress,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildAnnotatorsData(SubProject $subProject): array {
        /** @var array<int, int> $annotatorIds */
        $annotatorIds = AnnotationAssignment::query()
            ->where('sub_project_id', $subProject->id)
            ->pluck('user_id')
            ->all();

        /** @var \Illuminate\Support\Collection<int, int> $activeSubProjectIds */
        $activeSubProjectIds = collect([$subProject->id]);

        $annotatorsData = $this->annotatorService->getProjectAnnotatorsData($annotatorIds, $activeSubProjectIds);

        /** @var array<int, bool> $canFlagByAnnotatorId */
        $canFlagByAnnotatorId = $this->annotatorProjectLinksQuery->get($subProject->project_id, $annotatorIds)
            ->mapWithKeys(fn (AnnotatorOfProject $row): array => [$row->user_id => $row->can_flag])
            ->all();

        $flagCounts = $this->flagsQuery->get($annotatorIds);

        return array_map(
            fn (array $annotator): array => [
                ...$annotator,
                'can_flag' => ! is_int($annotator['id']) || (($canFlagByAnnotatorId[$annotator['id']] ?? true)),
                'flag_count' => is_int($annotator['id']) ? ($flagCounts[$annotator['id']][$subProject->id] ?? 0) : 0,
            ],
            $annotatorsData,
        );
    }

    /**
     * @return array<int, array{dataset_instance_id: int, annotated: int, planned_annotations: int, agreement: AgreementEnum}>
     */
    private function buildAnnotationsData(SubProject $subProject): array {
        return $this->annotationService->getAnnotationsData(
            $subProject->id,
            $subProject->project->annotationTask->task_type,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getAnnotatorsDataForCreate(int $projectId): array {
        /** @var array<int, int> $annotatorIds */
        $annotatorIds = $this->annotatorProjectLinksQuery->getAll($projectId)
            ->pluck('user_id')
            ->all();

        /** @var array<int, int> $subProjectIds */
        $subProjectIds = $this->subProjectIdsQuery->getAll()->all();
        $progressBySubProject = $this->getProgress($subProjectIds);

        /** @var \Illuminate\Support\Collection<int, int> $activeSubProjectIds */
        $activeSubProjectIds = collect($subProjectIds);

        return $this->annotatorService->getProjectAnnotatorsData($annotatorIds, $activeSubProjectIds, $progressBySubProject);
    }

    /**
     * @return array{dataset_id: int, dataset_name: string, size: int, previous_subset_last_index: int|null, from_instance: int, to_instance: int}
     */
    private function getSubsetDataForCreate(int $projectId): array {
        $info = $this->subsetInfoQuery->get($projectId);

        $size = $info['size'];
        $previousLastIndex = $info['previous_subset_last_index'];

        if ($previousLastIndex === null) {
            $fromInstance = 1;
        } else {
            $fromInstance = $previousLastIndex + 1;
            if ($fromInstance > $size) {
                $fromInstance = 1;
            }
        }

        return [
            'dataset_id' => $info['dataset_id'],
            'dataset_name' => $info['dataset_name'],
            'size' => $size,
            'previous_subset_last_index' => $previousLastIndex,
            'from_instance' => $fromInstance,
            'to_instance' => $size,
        ];
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
