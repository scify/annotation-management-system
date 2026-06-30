<?php

declare(strict_types=1);

namespace App\Services\Annotation;

use App\Data\AnnotationProgressStats;
use App\Enums\AgreementEnum;
use App\Enums\AnnotationTaskTypeEnum;
use App\Enums\ConfidenceEnum;
use App\Http\Requests\Annotation\SubmitAnnotationRequest;
use App\Models\AnnotationSession;
use App\Queries\Annotation\CreateAnnotationSessionQuery;
use App\Queries\Annotation\GetAnnotationAssignmentIdBySubProjectAndUserQuery;
use App\Queries\Annotation\GetAnnotationByIdQuery;
use App\Queries\Annotation\GetAnnotationSessionByIdQuery;
use App\Queries\Annotation\GetFlaggedAnnotationCountsQuery;
use App\Queries\Annotation\GetNextAnnotationIdQuery;
use App\Queries\Annotation\SubmitPendingAnnotationsQuery;
use App\Queries\SubProject\GetAnnotationCountsBySubProjectsQuery;
use App\Queries\SubProject\GetAnnotationsBySubProjectQuery;
use App\Queries\SubProject\GetSubProjectByIdQuery;
use App\Services\AnnotationTask\AnnotationTaskServiceFactory;
use RuntimeException;

readonly class AnnotationService {
    public function __construct(
        private GetAnnotationCountsBySubProjectsQuery $annotationCountsBySubProjectsQuery,
        private GetAnnotationsBySubProjectQuery $annotationsBySubProjectQuery,
        private GetAnnotationSessionByIdQuery $annotationSessionByIdQuery,
        private CreateAnnotationSessionQuery $createAnnotationSessionQuery,
        private GetAnnotationByIdQuery $annotationByIdQuery,
        private GetFlaggedAnnotationCountsQuery $flaggedAnnotationCountsQuery,
        private AnnotationTaskServiceFactory $taskServiceFactory,
        private GetSubProjectByIdQuery $subProjectByIdQuery,
        private GetAnnotationAssignmentIdBySubProjectAndUserQuery $annotationAssignmentIdQuery,
        private SubmitPendingAnnotationsQuery $submitPendingAnnotationsQuery,
        private GetNextAnnotationIdQuery $nextAnnotationIdQuery,
    ) {}

    /** @return array<string, mixed> */
    public function submitAnnotation(
        SubmitAnnotationRequest $request,
        int $subProjectId,
        int $userId,
    ): array {
        $mode = $request->string('mode')->toString();

        if (! in_array($mode, ['strict', 'flexible'], true)) {
            $mode = 'strict';
        }

        $annotationId = $request->integer('annotation_id');
        /** @var array<string, mixed> $annotations */
        $annotations = $request->array('annotations');
        $pending = $request->boolean('pending');
        $rawConfidence = $request->string('confidence')->toString();
        $confidence = $rawConfidence !== '' ? ConfidenceEnum::from($rawConfidence) : null;

        $taskType = $this->resolveTaskType($subProjectId);
        $this->taskServiceFactory->make($taskType)->save($annotationId, $annotations, $pending, $confidence);

        $annotationAssignmentId = $this->annotationAssignmentIdQuery->get($subProjectId, $userId);

        if ($mode === 'flexible') {
            $nextAnnotationId = $annotationId;
        } else {
            $nextAnnotationId = $annotationAssignmentId !== null
                ? $this->nextAnnotationIdQuery->get($annotationAssignmentId)
                : null;
        }

        if ($annotationAssignmentId === null || $nextAnnotationId === null) {
            return $this->getInitialViewData($subProjectId, $mode, $userId);
        }

        $annotationSessionId = $this->startSession($annotationAssignmentId, $nextAnnotationId);

        return $this->getDataForShowAnnotation($subProjectId, $mode, $userId, $annotationSessionId, $nextAnnotationId);
    }

    public function submitPending(int $subProjectId, int $userId): void {
        $annotationAssignmentId = $this->annotationAssignmentIdQuery->get($subProjectId, $userId);

        if ($annotationAssignmentId === null) {
            return;
        }

        $this->submitPendingAnnotationsQuery->submit($annotationAssignmentId, $userId);
    }

    public function startSession(int $annotationAssignmentId, int $nextAnnotationId): int {
        return $this->createAnnotationSessionQuery->create($annotationAssignmentId, $nextAnnotationId);
    }

    /** @return array<string, mixed> */
    public function getInitialViewData(int $subProjectId, string $mode, int $userId): array {
        $annotationAssignmentId = $this->annotationAssignmentIdQuery->get($subProjectId, $userId);
        $nextAnnotationId = $annotationAssignmentId !== null
            ? $this->nextAnnotationIdQuery->get($annotationAssignmentId)
            : null;

        if ($annotationAssignmentId !== null && $nextAnnotationId !== null) {
            $annotationSessionId = $this->startSession($annotationAssignmentId, $nextAnnotationId);

            return [
                'annotationAssignmentId' => $annotationAssignmentId,
                'nextAnnotationId' => $nextAnnotationId,
                ...$this->getDataForShowAnnotation($subProjectId, $mode, $userId, $annotationSessionId, $nextAnnotationId),
            ];
        }

        return [
            'annotationAssignmentId' => $annotationAssignmentId,
            'nextAnnotationId' => $nextAnnotationId,
            'subProjectId' => $subProjectId,
            'mode' => $mode,
            'annotationProgressData' => $this->getAnnotationProgressData($subProjectId, $mode, $userId, null),
            ...$this->getSubProjectNames($subProjectId),
        ];
    }

    /** @return array<string, mixed> */
    public function getDataForShowAnnotation(int $subProjectId, string $mode, int $userId, int $annotationSessionId, int $nextAnnotationId): array {
        return [
            'subProjectId' => $subProjectId,
            'mode' => $mode,
            'annotationProgressData' => $this->getAnnotationProgressData($subProjectId, $mode, $userId, $annotationSessionId),
            'annotationTaskData' => $this->getAnnotationTaskData($nextAnnotationId, $subProjectId),
            ...$this->getSubProjectNames($subProjectId),
        ];
    }

    /**
     * Returns per-dataset-instance annotation data for a sub-project.
     *
     * @return array<int, array{
     *     dataset_instance_id: int,
     *     annotated: int,
     *     planned_annotations: int,
     *     agreement: AgreementEnum,
     *     annotations: array<int, array{
     *         id: int,
     *         annotator_data: array{user_id: int, username: string, role: string|null},
     *         last_edited_by_data: array{user_id: int, username: string|null, role: string|null}|null,
     *         updated_at: string|null,
     *         confidence: ConfidenceEnum|null,
     *         status: string
     *     }>
     * }>
     */
    public function getAnnotationsData(int $subProjectId, AnnotationTaskTypeEnum $taskType): array {
        $taskService = $this->taskServiceFactory->make($taskType);
        $rows = $this->annotationsBySubProjectQuery->get($subProjectId);

        $result = [];

        foreach ($rows as $datasetInstanceId => $instanceData) {
            $result[] = [
                'dataset_instance_id' => $datasetInstanceId,
                'annotated' => $instanceData['annotated'],
                'planned_annotations' => $instanceData['planned_annotations'],
                'agreement' => $taskService->computeAgreement($instanceData['annotations_values']),
                'annotations' => $instanceData['annotations'],
            ];
        }

        return $result;
    }

    private function resolveTaskType(int $subProjectId): AnnotationTaskTypeEnum {
        $subProject = $this->subProjectByIdQuery->getWithProjectAndAnnotationTask($subProjectId);
        $project = $subProject->project ?? throw new RuntimeException(sprintf('SubProject %d has no parent project.', $subProjectId));
        $annotationTask = $project->annotationTask ?? throw new RuntimeException(sprintf('Project %d has no annotation task.', $project->id));

        return $annotationTask->task_type;
    }

    /** @return array{projectName: string, subProjectName: string} */
    private function getSubProjectNames(int $subProjectId): array {
        $subProject = $this->subProjectByIdQuery->getWithProject($subProjectId);
        $project = $subProject->project ?? throw new RuntimeException(sprintf('SubProject %d has no parent project.', $subProjectId));

        return [
            'projectName' => $project->name,
            'subProjectName' => $subProject->name,
        ];
    }

    /** @return array<string, mixed> */
    private function getAnnotationProgressData(int $subProjectId, string $mode, int $userId, ?int $annotationSessionId): array {
        $counts = $this->annotationCountsBySubProjectsQuery->get([$subProjectId], $userId);
        $stats = AnnotationProgressStats::fromCounts(
            $counts[$subProjectId] ?? ['pending_count' => 0, 'submitted_count' => 0, 'not_annotated_count' => 0],
        );

        $session = $annotationSessionId !== null ? $this->annotationSessionByIdQuery->get($annotationSessionId) : null;
        $flagCounts = $this->flaggedAnnotationCountsQuery->get($subProjectId, $userId);

        $data = [
            'submitted_count' => $stats->submittedCount,
            'not_annotated_count' => $stats->notAnnotatedCount,
            'submitted_pct' => $stats->submittedPct,
            'session_annotations_count' => $session instanceof AnnotationSession ? $session->session_annotations_count : 0,
            'number_of_flagged_instances' => $flagCounts['flagged_count'],
            'number_of_replied_flagged_instances' => $flagCounts['replied_flagged_count'],
        ];

        if ($mode === 'flexible') {
            $data['pending_count'] = $stats->pendingCount;
            $data['submitted_and_pending_pct'] = $stats->submittedAndPendingPct;
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function getAnnotationTaskData(int $nextAnnotationId, int $subProjectId): array {
        $annotation = $this->annotationByIdQuery->get($nextAnnotationId);
        $taskType = $this->resolveTaskType($subProjectId);
        $taskService = $this->taskServiceFactory->make($taskType);

        return [
            'annotator_instance_index' => $annotation->annotator_instance_index,
            ...$taskService->getTaskRelatedData($annotation->dataset_instance_id, $subProjectId),
        ];
    }
}
