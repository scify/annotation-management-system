<?php

declare(strict_types=1);

namespace App\Services\SubProject;

use App\Enums\AgreementEnum;
use App\Enums\ProjectStatusEnum;
use App\Models\AnnotatorOfProject;
use App\Models\SubProject;
use App\Queries\Annotator\GetAnnotatorProjectLinksByProjectQuery;
use App\Queries\Annotator\GetCountsOfFlagsQuery;
use App\Queries\Project\GetProjectBasicDataQuery;
use App\Queries\Project\GetSubProjectIdsQuery;
use App\Queries\Project\GetSubsetInfoByProjectQuery;
use App\Queries\SubProject\GetAnnotatorIdsBySubProjectQuery;
use App\Queries\SubProject\GetSubProjectByProjectAndIdQuery;
use App\Services\Annotation\AnnotationService;
use App\Services\Annotation\AnnotatorService;
use Illuminate\Support\Collection;

readonly class SubProjectReadService {
    public function __construct(
        private SubProjectWriteService $subProjectService,
        private AnnotatorService $annotatorService,
        private AnnotationService $annotationService,
        private GetAnnotatorProjectLinksByProjectQuery $annotatorProjectLinksQuery,
        private GetCountsOfFlagsQuery $flagsQuery,
        private GetProjectBasicDataQuery $projectBasicDataQuery,
        private GetSubProjectIdsQuery $subProjectIdsQuery,
        private GetSubsetInfoByProjectQuery $subsetInfoQuery,
        private GetAnnotatorIdsBySubProjectQuery $annotatorIdsBySubProjectQuery,
        private GetSubProjectByProjectAndIdQuery $subProjectByProjectAndIdQuery,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getDataForCreateSubProject(int $projectId): array {
        return [
            'project_data' => $this->projectBasicDataQuery->get($projectId),
            'annotators_data' => $this->getAnnotatorsDataForCreate($projectId),
            'subset_data' => $this->getSubsetDataForCreate($projectId),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataForEditSubProject(int $projectId, int $subprojectId): array {
        $subProject = $this->subProjectByProjectAndIdQuery->getForEdit($projectId, $subprojectId);

        return [
            'project_name' => $subProject->project->name,
            'subproject_data' => $this->buildSubProjectData($subProject),
            'annotators_data' => $this->buildAnnotatorsData($subProject),
            'annotations_data' => $this->buildAnnotationsData($subProject),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataForAddAnnotators(int $projectId, int $subprojectId): array {
        $subProject = $this->subProjectByProjectAndIdQuery->getForAddAnnotators($projectId, $subprojectId);

        /** @var array<int, int> $projectAnnotatorIds */
        $projectAnnotatorIds = $this->annotatorProjectLinksQuery->getAll($projectId)
            ->pluck('user_id')
            ->all();

        /** @var array<int, true> $subProjectAnnotatorIds */
        $subProjectAnnotatorIds = array_flip($this->annotatorIdsBySubProjectQuery->get($subprojectId));

        $availableIds = array_values(array_filter(
            $projectAnnotatorIds,
            fn (int $id): bool => ! isset($subProjectAnnotatorIds[$id]),
        ));

        /** @var array<int, int> $allSubProjectIds */
        $allSubProjectIds = $this->subProjectIdsQuery->getAll()->all();
        $progressBySubProject = $this->subProjectService->getProgress($allSubProjectIds);

        /** @var Collection<int, int> $activeSubProjectIds */
        $activeSubProjectIds = collect($allSubProjectIds);

        return [
            'project_id' => $subProject->project_id,
            'project_name' => $subProject->project->name,
            'subproject_id' => $subProject->id,
            'subproject_name' => $subProject->name,
            'annotators_data' => $this->annotatorService->getProjectAnnotatorsData(
                $availableIds, $activeSubProjectIds, $progressBySubProject
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSubProjectData(SubProject $subProject): array {
        $progress = $this->subProjectService->getProgress([$subProject->id]);
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
            'is_instance_shuffled_per_annotator' => $this->annotatorIdsBySubProjectQuery->isInstanceShuffled($subProject->id),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildAnnotatorsData(SubProject $subProject): array {
        /** @var array<int, int> $annotatorIds */
        $annotatorIds = $this->annotatorIdsBySubProjectQuery->get($subProject->id);

        /** @var Collection<int, int> $activeSubProjectIds */
        $activeSubProjectIds = collect([$subProject->id]);

        $progressBySubProject = $this->subProjectService->getProgress([$subProject->id]);
        $annotatorsData = $this->annotatorService->getProjectAnnotatorsData($annotatorIds, $activeSubProjectIds, $progressBySubProject);

        /** @var array<int, bool> $canFlagByAnnotatorId */
        $canFlagByAnnotatorId = $this->annotatorProjectLinksQuery->get($subProject->project_id, $annotatorIds)
            ->mapWithKeys(fn (AnnotatorOfProject $row): array => [$row->user_id => $row->can_flag])
            ->all();

        $flagCounts = $this->flagsQuery->get($annotatorIds);
        $canBeRemoved = $subProject->status === ProjectStatusEnum::PENDING;

        return array_map(
            function (array $annotator) use ($canFlagByAnnotatorId, $flagCounts, $subProject, $canBeRemoved): array {
                $annotator['can_flag'] = ! is_int($annotator['id']) || (($canFlagByAnnotatorId[$annotator['id']] ?? true));
                $annotator['flag_count'] = is_int($annotator['id']) ? ($flagCounts[$annotator['id']][$subProject->id] ?? 0) : 0;
                $annotator['can_be_removed'] = $canBeRemoved;
                unset($annotator['active_subprojects_count'], $annotator['active_projects_count']);

                return $annotator;
            },
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
        $progressBySubProject = $this->subProjectService->getProgress($subProjectIds);

        /** @var Collection<int, int> $activeSubProjectIds */
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
}
