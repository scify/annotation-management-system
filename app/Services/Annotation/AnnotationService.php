<?php

declare(strict_types=1);

namespace App\Services\Annotation;

use App\Data\AnnotationProgressStats;
use App\Data\QuickLinkData;
use App\Enums\AgreementEnum;
use App\Enums\AnnotationInstanceFilterEnum;
use App\Enums\AnnotationTaskTypeEnum;
use App\Enums\ConfidenceEnum;
use App\Http\Requests\Annotation\FlagAnnotationRequest;
use App\Http\Requests\Annotation\SendToManagerAnnotationRequest;
use App\Http\Requests\Annotation\SubmitAnnotationRequest;
use App\Models\AnnotationSession;
use App\Queries\Annotation\CreateAnnotationSessionQuery;
use App\Queries\Annotation\FlagAnnotationInstanceQuery;
use App\Queries\Annotation\GetAnnotationAssignmentIdBySubProjectAndUserQuery;
use App\Queries\Annotation\GetAnnotationByIdQuery;
use App\Queries\Annotation\GetAnnotationSessionByIdQuery;
use App\Queries\Annotation\GetFlaggedAnnotationCountsQuery;
use App\Queries\Annotation\GetNextAnnotationIdQuery;
use App\Queries\Annotation\IncrementAnnotationSessionCountQuery;
use App\Queries\Annotation\MarkMessageToManagersQuery;
use App\Queries\Annotation\StopAnnotationSessionQuery;
use App\Queries\Annotation\SubmitPendingAnnotationsQuery;
use App\Queries\Annotation\UpdateAnnotationSessionNextAnnotationQuery;
use App\Queries\Annotator\GetAnnotatorProjectLinksByProjectQuery;
use App\Queries\Notification\GetFlagThreadStatusQuery;
use App\Queries\Project\GetManagerIdsByProjectsQuery;
use App\Queries\SubProject\GetAnnotationCountsBySubProjectsQuery;
use App\Queries\SubProject\GetAnnotationsBySubProjectQuery;
use App\Queries\SubProject\GetSubProjectByIdQuery;
use App\Services\AnnotationTask\AnnotationTaskService;
use App\Services\AnnotationTask\AnnotationTaskServiceFactory;
use App\Services\Notification\FlagNotificationService;
use App\Services\Notification\InstanceRelatedNotificationService;
use RuntimeException;

readonly class AnnotationService {
    public function __construct(
        private GetAnnotationCountsBySubProjectsQuery $annotationCountsBySubProjectsQuery,
        private GetAnnotationsBySubProjectQuery $annotationsBySubProjectQuery,
        private GetAnnotationSessionByIdQuery $annotationSessionByIdQuery,
        private CreateAnnotationSessionQuery $createAnnotationSessionQuery,
        private GetAnnotationByIdQuery $annotationByIdQuery,
        private GetFlaggedAnnotationCountsQuery $flaggedAnnotationCountsQuery,
        private IncrementAnnotationSessionCountQuery $incrementAnnotationSessionCountQuery,
        private AnnotationTaskServiceFactory $taskServiceFactory,
        private GetSubProjectByIdQuery $subProjectByIdQuery,
        private GetAnnotationAssignmentIdBySubProjectAndUserQuery $annotationAssignmentIdQuery,
        private SubmitPendingAnnotationsQuery $submitPendingAnnotationsQuery,
        private GetNextAnnotationIdQuery $nextAnnotationIdQuery,
        private FlagNotificationService $flagNotificationService,
        private GetManagerIdsByProjectsQuery $getManagerIdsByProjectsQuery,
        private FlagAnnotationInstanceQuery $flagAnnotationInstanceQuery,
        private GetAnnotatorProjectLinksByProjectQuery $annotatorProjectLinksQuery,
        private InstanceRelatedNotificationService $instanceRelatedNotificationService,
        private MarkMessageToManagersQuery $markMessageToManagersQuery,
        private GetFlagThreadStatusQuery $flagThreadStatusQuery,
        private StopAnnotationSessionQuery $stopAnnotationSessionQuery,
        private UpdateAnnotationSessionNextAnnotationQuery $updateAnnotationSessionNextAnnotationQuery,
    ) {}

    /**
     * Persists the submitted annotation. The view is rebuilt by the subsequent
     * redirect to GET `annotation.show`, so this method only performs the write.
     */
    public function submitAnnotation(SubmitAnnotationRequest $request, int $subProjectId, int $userId): void {
        $annotationId = $request->integer('annotation_id');
        /** @var array<string, mixed> $annotations */
        $annotations = $request->array('annotations');
        $pending = $request->boolean('pending');
        $rawConfidence = $request->string('confidence')->toString();
        $confidence = $rawConfidence !== '' ? ConfidenceEnum::from($rawConfidence) : null;

        $taskType = $this->resolveTaskContext($subProjectId)['taskType'];
        $this->taskServiceFactory->make($taskType)->save($annotationId, $annotations, $pending, $confidence, $userId);
        $this->incrementAnnotationSessionCountQuery->increment($request->integer('annotation_session_id'));
    }

    public function sendToManager(SendToManagerAnnotationRequest $request, int $subProjectId, int $userId): void {
        $annotationAssignmentId = $this->annotationAssignmentIdQuery->get($subProjectId, $userId);

        if ($annotationAssignmentId !== null) {
            $annotatorInstanceIndex = $request->integer('annotator_instance_index');
            $annotationId = $this->annotationByIdQuery->getIdByAssignmentAndIndex($annotationAssignmentId, $annotatorInstanceIndex);
            $message = $request->string('message')->toString();

            $flagThreadId = $annotationId !== null
                ? $this->annotationByIdQuery->getAnnotationData($annotationId)->flag_notification_thread_id
                : null;

            if ($flagThreadId !== null) {
                $this->instanceRelatedNotificationService->reply(
                    notificationThreadId: $flagThreadId,
                    senderUserId: $userId,
                    body: $message,
                );
                $this->markMessageToManagersQuery->mark($annotationAssignmentId, $annotatorInstanceIndex, $flagThreadId);
            } else {
                $subProject = $this->subProjectByIdQuery->getWithProject($subProjectId);
                $project = $subProject->project ?? throw new RuntimeException(sprintf('SubProject %d has no parent project.', $subProjectId));
                $recipientIds = $this->getManagerIdsByProjectsQuery->getAccepted([$project->id], $userId);

                $notification = $this->instanceRelatedNotificationService->createNotification(
                    recipientUserIds: $recipientIds,
                    body: $message,
                    senderUserId: $userId,
                    firstQuickLink: new QuickLinkData('Instance#', $annotationId !== null ? route('annotation.instance.show', ['subProject' => $subProjectId, 'annotationId' => $annotationId]) : route('annotation.show', ['subProject' => $subProjectId]), $annotationId),
                    secondQuickLink: new QuickLinkData('Project', route('projects.show', ['id' => $project->id])),
                );
                $this->markMessageToManagersQuery->mark($annotationAssignmentId, $annotatorInstanceIndex, $notification->notification_thread_id);
            }
        }
    }

    public function flagInstance(FlagAnnotationRequest $request, int $subProjectId, int $userId): void {
        $annotationAssignmentId = $this->annotationAssignmentIdQuery->get($subProjectId, $userId);

        if ($annotationAssignmentId !== null) {
            $subProject = $this->subProjectByIdQuery->getWithProject($subProjectId);
            $project = $subProject->project ?? throw new RuntimeException(sprintf('SubProject %d has no parent project.', $subProjectId));

            $recipientIds = $this->getManagerIdsByProjectsQuery->get([$project->id], $userId);

            $annotatorInstanceIndex = $request->integer('annotator_instance_index');
            $annotationId = $this->annotationByIdQuery->getIdByAssignmentAndIndex($annotationAssignmentId, $annotatorInstanceIndex);

            $notification = $this->flagNotificationService->createNotification(
                recipientUserIds: $recipientIds,
                body: $request->string('flag_message')->toString(),
                senderUserId: $userId,
                firstQuickLink: new QuickLinkData('Flagged Instance#', $annotationId !== null ? route('annotation.instance.show', ['subProject' => $subProjectId, 'annotationId' => $annotationId]) : route('annotation.show', ['subProject' => $subProjectId]), $annotationId),
                secondQuickLink: new QuickLinkData('Project', route('projects.show', ['id' => $project->id])),
            );

            $this->flagAnnotationInstanceQuery->flag(
                $annotationAssignmentId,
                $request->integer('annotator_instance_index'),
                $notification->notification_thread_id,
            );
        }
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

    public function stopSession(int $annotationSessionId): void {
        $this->stopAnnotationSessionQuery->stop($annotationSessionId);
    }

    /** @return array<string, mixed> */
    public function getAnnotationViewData(int $subProjectId, int $userId, AnnotationInstanceFilterEnum $activeFilter, ?int $existingSessionId = null): array {
        $flags = $this->getSubProjectSettings($subProjectId);
        $annotationAssignmentId = $this->annotationAssignmentIdQuery->get($subProjectId, $userId);
        $nextAnnotationId = $annotationAssignmentId !== null
            ? $this->nextAnnotationIdQuery->get($annotationAssignmentId)
            : null;

        if ($annotationAssignmentId !== null && $nextAnnotationId !== null) {
            $annotationSessionId = $existingSessionId ?? $this->startSession($annotationAssignmentId, $nextAnnotationId);
            if ($existingSessionId !== null) {
                $this->updateAnnotationSessionNextAnnotationQuery->update($annotationSessionId, $nextAnnotationId);
            }

            return [
                'annotationAssignmentId' => $annotationAssignmentId,
                'nextAnnotationId' => $nextAnnotationId,
                ...$this->getDataForShowAnnotation($subProjectId, $userId, $annotationSessionId, $nextAnnotationId, $activeFilter),
            ];
        }

        $stats = $this->fetchAnnotationStats($subProjectId, $userId);
        $activeFilter = $this->resolveActiveFilter($activeFilter, $flags['can_submit_all_pending'], $stats);

        $data = [
            'annotationAssignmentId' => $annotationAssignmentId,
            'nextAnnotationId' => $nextAnnotationId,
            'subProjectId' => $subProjectId,
            'can_navigate' => $flags['can_navigate'],
            'can_submit_all_pending' => $flags['can_submit_all_pending'],
            'annotationProgressData' => $this->getAnnotationProgressData($stats, $flags['can_navigate'], $subProjectId, $userId, null),
            ...$this->getSubProjectNames($subProjectId),
        ];

        if ($flags['can_navigate']) {
            $data['instance_filters'] = $this->buildInstanceFilters($flags['can_submit_all_pending'], $activeFilter, $stats);
        }

        return $data;
    }

    /** @return array<string, mixed> */
    public function getDataForShowAnnotation(int $subProjectId, int $userId, int $annotationSessionId, int $nextAnnotationId, AnnotationInstanceFilterEnum $activeFilter): array {
        $flags = $this->getSubProjectSettings($subProjectId);
        $stats = $this->fetchAnnotationStats($subProjectId, $userId);
        $activeFilter = $this->resolveActiveFilter($activeFilter, $flags['can_submit_all_pending'], $stats);

        $data = [
            'subProjectId' => $subProjectId,
            'can_navigate' => $flags['can_navigate'],
            'can_submit_all_pending' => $flags['can_submit_all_pending'],
            'annotationSessionId' => $annotationSessionId,
            'can_flag' => $this->getCanFlag($subProjectId, $userId),
            'annotationProgressData' => $this->getAnnotationProgressData($stats, $flags['can_navigate'], $subProjectId, $userId, $annotationSessionId),
            'annotationTaskData' => $this->getAnnotationTaskData($nextAnnotationId, $subProjectId, $userId),
            ...$this->getSubProjectNames($subProjectId),
        ];

        if ($flags['can_navigate']) {
            $data['instance_filters'] = $this->buildInstanceFilters($flags['can_submit_all_pending'], $activeFilter, $stats);
        }

        return $data;
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
     *         status: string,
     *         message_to_managers: int|null
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

    /** @return array<string, mixed> */
    public function getAnnotationInstanceViewData(int $subProjectId, int $annotationId): array {
        $annotation = $this->annotationByIdQuery->get($annotationId);
        $taskContext = $this->resolveTaskContext($subProjectId);
        $taskService = $this->taskServiceFactory->make($taskContext['taskType']);

        return [
            'subProjectId' => $subProjectId,
            'annotationTaskData' => [
                'annotator_instance_index' => $annotation->annotator_instance_index,
                ...$taskService->getTaskRelatedData($annotation->dataset_instance_id, $taskContext['projectId']),
            ],
            ...$this->getSubProjectNames($subProjectId),
        ];
    }

    /** @return array{taskType: AnnotationTaskTypeEnum, projectId: int} */
    private function resolveTaskContext(int $subProjectId): array {
        $subProject = $this->subProjectByIdQuery->getWithProjectAndAnnotationTask($subProjectId);
        $project = $subProject->project ?? throw new RuntimeException(sprintf('SubProject %d has no parent project.', $subProjectId));
        $annotationTask = $project->annotationTask ?? throw new RuntimeException(sprintf('Project %d has no annotation task.', $project->id));

        return ['taskType' => $annotationTask->task_type, 'projectId' => $project->id];
    }

    private function getCanFlag(int $subProjectId, int $userId): bool {
        $subProject = $this->subProjectByIdQuery->getWithProject($subProjectId);
        $project = $subProject->project ?? throw new RuntimeException(sprintf('SubProject %d has no parent project.', $subProjectId));

        return $this->annotatorProjectLinksQuery->canFlag($userId, $project->id);
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

    private function resolveActiveFilter(AnnotationInstanceFilterEnum $activeFilter, bool $canSubmitAllPending, AnnotationProgressStats $stats): AnnotationInstanceFilterEnum {
        $canBeSelected = match ($activeFilter) {
            AnnotationInstanceFilterEnum::All => true,
            AnnotationInstanceFilterEnum::NotAnnotated => $stats->notAnnotatedCount > 0,
            AnnotationInstanceFilterEnum::Pending => $canSubmitAllPending && $stats->pendingCount > 0,
            AnnotationInstanceFilterEnum::Submitted => $stats->submittedCount > 0,
        };

        return $canBeSelected ? $activeFilter : AnnotationInstanceFilterEnum::All;
    }

    /**
     * @return array<string, array{is_selected: bool, can_be_selected: bool}>
     */
    private function buildInstanceFilters(bool $canSubmitAllPending, AnnotationInstanceFilterEnum $activeFilter, AnnotationProgressStats $stats): array {
        $filterKeys = $canSubmitAllPending
            ? [AnnotationInstanceFilterEnum::All, AnnotationInstanceFilterEnum::NotAnnotated, AnnotationInstanceFilterEnum::Pending, AnnotationInstanceFilterEnum::Submitted]
            : [AnnotationInstanceFilterEnum::All, AnnotationInstanceFilterEnum::NotAnnotated, AnnotationInstanceFilterEnum::Submitted];

        $filters = [];
        foreach ($filterKeys as $filter) {
            $canBeSelected = match ($filter) {
                AnnotationInstanceFilterEnum::All => true,
                AnnotationInstanceFilterEnum::NotAnnotated => $stats->notAnnotatedCount > 0,
                AnnotationInstanceFilterEnum::Pending => $canSubmitAllPending && $stats->pendingCount > 0,
                AnnotationInstanceFilterEnum::Submitted => $stats->submittedCount > 0,
            };

            $filters[$filter->value] = [
                'is_selected' => $filter === $activeFilter,
                'can_be_selected' => $canBeSelected,
            ];
        }

        return $filters;
    }

    /** @return array{can_navigate: bool, can_submit_all_pending: bool} */
    private function getSubProjectSettings(int $subProjectId): array {
        $subProject = $this->subProjectByIdQuery->get($subProjectId);

        return [
            'can_navigate' => $subProject->flexible,
            'can_submit_all_pending' => ! $subProject->auto_submission,
        ];
    }

    private function fetchAnnotationStats(int $subProjectId, int $userId): AnnotationProgressStats {
        $counts = $this->annotationCountsBySubProjectsQuery->get([$subProjectId], $userId);

        return AnnotationProgressStats::fromCounts(
            $counts[$subProjectId] ?? ['pending_count' => 0, 'submitted_count' => 0, 'not_annotated_count' => 0],
        );
    }

    /** @return array<string, mixed> */
    private function getAnnotationProgressData(AnnotationProgressStats $stats, bool $canNavigate, int $subProjectId, int $userId, ?int $annotationSessionId): array {

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

        if ($canNavigate) {
            $data['pending_count'] = $stats->pendingCount;
            $data['submitted_and_pending_pct'] = $stats->submittedAndPendingPct;
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function getAnnotationTaskData(int $nextAnnotationId, int $subProjectId, int $userId): array {
        $annotation = $this->annotationByIdQuery->get($nextAnnotationId);
        $taskContext = $this->resolveTaskContext($subProjectId);
        $taskService = $this->taskServiceFactory->make($taskContext['taskType']);

        return [
            'annotator_instance_index' => $annotation->annotator_instance_index,
            'annotationData' => $this->getAnnotationData($nextAnnotationId, $userId, $taskService, $taskContext['projectId']),
            ...$taskService->getTaskRelatedData($annotation->dataset_instance_id, $taskContext['projectId']),
        ];
    }

    /** @return array{is_flagged: bool, flag_notification_thread_id: int|null, is_replied: bool|null, is_reply_read: bool|null, is_submitted: bool, annotations: array<string, mixed>, confidence: string|null} */
    private function getAnnotationData(int $annotationId, int $userId, AnnotationTaskService $taskService, int $projectId): array {
        $annotation = $this->annotationByIdQuery->getAnnotationData($annotationId);

        $flagThreadId = $annotation->flag_notification_thread_id;
        $isReplied = null;
        $isReplyRead = null;

        if ($flagThreadId !== null) {
            $status = $this->flagThreadStatusQuery->get($flagThreadId, $userId);
            $isReplied = $status['is_replied'];
            $isReplyRead = $status['is_reply_read'];
        }

        $annotations = $annotation->annotations ?? $taskService->getAnnotationSchema($projectId);

        return [
            'is_flagged' => $annotation->isFlagged(),
            'flag_notification_thread_id' => $flagThreadId,
            'is_replied' => $isReplied,
            'is_reply_read' => $isReplyRead,
            'is_submitted' => $annotation->isAnnotated(),
            'annotations' => $annotations,
            'confidence' => $annotation->confidence?->value,
        ];
    }
}
