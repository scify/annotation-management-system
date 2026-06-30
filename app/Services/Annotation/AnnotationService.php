<?php

declare(strict_types=1);

namespace App\Services\Annotation;

use App\Data\AnnotationProgressStats;
use App\Data\QuickLinkData;
use App\Enums\AgreementEnum;
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
use App\Queries\Annotation\MarkMessageToManagersQuery;
use App\Queries\Annotation\SubmitPendingAnnotationsQuery;
use App\Queries\Annotator\GetAnnotatorProjectLinksByProjectQuery;
use App\Queries\Notification\GetFlagThreadStatusQuery;
use App\Queries\Project\GetManagerIdsByProjectsQuery;
use App\Queries\SubProject\GetAnnotationCountsBySubProjectsQuery;
use App\Queries\SubProject\GetAnnotationsBySubProjectQuery;
use App\Queries\SubProject\GetSubProjectByIdQuery;
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
    ) {}

    /** @return array<string, mixed> */
    public function submitAnnotation(
        SubmitAnnotationRequest $request,
        int $subProjectId,
        int $userId,
    ): array {
        $annotationId = $request->integer('annotation_id');
        /** @var array<string, mixed> $annotations */
        $annotations = $request->array('annotations');
        $pending = $request->boolean('pending');
        $rawConfidence = $request->string('confidence')->toString();
        $confidence = $rawConfidence !== '' ? ConfidenceEnum::from($rawConfidence) : null;

        $taskType = $this->resolveTaskType($subProjectId);
        $this->taskServiceFactory->make($taskType)->save($annotationId, $annotations, $pending, $confidence);

        $subProject = $this->subProjectByIdQuery->get($subProjectId);
        $annotationAssignmentId = $this->annotationAssignmentIdQuery->get($subProjectId, $userId);

        if ($subProject->flexible) {
            $nextAnnotationId = $annotationId;
        } else {
            $nextAnnotationId = $annotationAssignmentId !== null
                ? $this->nextAnnotationIdQuery->get($annotationAssignmentId)
                : null;
        }

        if ($annotationAssignmentId === null || $nextAnnotationId === null) {
            return $this->getInitialViewData($subProjectId, $userId);
        }

        $annotationSessionId = $request->integer('annotation_session_id');

        return $this->getDataForShowAnnotation($subProjectId, $userId, $annotationSessionId, $nextAnnotationId);
    }

    /** @return array<string, mixed> */
    public function sendToManager(SendToManagerAnnotationRequest $request, int $subProjectId, int $userId): array {
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
                    firstQuickLink: new QuickLinkData('Instance#', route('annotation.show', ['subProject' => $subProjectId, 'user_id' => $userId, 'annotator_instance_index' => $annotatorInstanceIndex]), $annotationId),
                    secondQuickLink: new QuickLinkData('Project', route('projects.show', ['id' => $project->id])),
                );
                $this->markMessageToManagersQuery->mark($annotationAssignmentId, $annotatorInstanceIndex, $notification->notification_thread_id);
            }
        }

        $annotationSessionId = $request->integer('annotation_session_id');
        $nextAnnotationId = $annotationAssignmentId !== null
            ? $this->nextAnnotationIdQuery->get($annotationAssignmentId)
            : null;

        if ($annotationAssignmentId === null || $nextAnnotationId === null) {
            return $this->getInitialViewData($subProjectId, $userId);
        }

        return $this->getDataForShowAnnotation($subProjectId, $userId, $annotationSessionId, $nextAnnotationId);
    }

    /** @return array<string, mixed> */
    public function flagInstance(FlagAnnotationRequest $request, int $subProjectId, int $userId): array {
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
                firstQuickLink: new QuickLinkData('Flagged Instance#', route('annotation.show', ['subProject' => $subProjectId, 'user_id' => $userId, 'annotator_instance_index' => $annotatorInstanceIndex]), $annotationId),
                secondQuickLink: new QuickLinkData('Project', route('projects.show', ['id' => $project->id])),
            );

            $this->flagAnnotationInstanceQuery->flag(
                $annotationAssignmentId,
                $request->integer('annotator_instance_index'),
                $notification->notification_thread_id,
            );
        }

        $annotationSessionId = $request->integer('annotation_session_id');
        $nextAnnotationId = $annotationAssignmentId !== null
            ? $this->nextAnnotationIdQuery->get($annotationAssignmentId)
            : null;

        if ($annotationAssignmentId === null || $nextAnnotationId === null) {
            return $this->getInitialViewData($subProjectId, $userId);
        }

        return $this->getDataForShowAnnotation($subProjectId, $userId, $annotationSessionId, $nextAnnotationId);
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
    public function getInitialViewData(int $subProjectId, int $userId): array {
        $flags = $this->getSubProjectSettings($subProjectId);
        $annotationAssignmentId = $this->annotationAssignmentIdQuery->get($subProjectId, $userId);
        $nextAnnotationId = $annotationAssignmentId !== null
            ? $this->nextAnnotationIdQuery->get($annotationAssignmentId)
            : null;

        if ($annotationAssignmentId !== null && $nextAnnotationId !== null) {
            $annotationSessionId = $this->startSession($annotationAssignmentId, $nextAnnotationId);

            return [
                'annotationAssignmentId' => $annotationAssignmentId,
                'nextAnnotationId' => $nextAnnotationId,
                ...$this->getDataForShowAnnotation($subProjectId, $userId, $annotationSessionId, $nextAnnotationId),
            ];
        }

        return [
            'annotationAssignmentId' => $annotationAssignmentId,
            'nextAnnotationId' => $nextAnnotationId,
            'subProjectId' => $subProjectId,
            'can_navigate' => $flags['can_navigate'],
            'can_submit_all_pending' => $flags['can_submit_all_pending'],
            'annotationProgressData' => $this->getAnnotationProgressData($subProjectId, $flags['can_navigate'], $userId, null),
            ...$this->getSubProjectNames($subProjectId),
        ];
    }

    /** @return array<string, mixed> */
    public function getDataForShowAnnotation(int $subProjectId, int $userId, int $annotationSessionId, int $nextAnnotationId): array {
        $flags = $this->getSubProjectSettings($subProjectId);

        return [
            'subProjectId' => $subProjectId,
            'can_navigate' => $flags['can_navigate'],
            'can_submit_all_pending' => $flags['can_submit_all_pending'],
            'annotationSessionId' => $annotationSessionId,
            'can_flag' => $this->getCanFlag($subProjectId, $userId),
            'annotationProgressData' => $this->getAnnotationProgressData($subProjectId, $flags['can_navigate'], $userId, $annotationSessionId),
            'annotationTaskData' => $this->getAnnotationTaskData($nextAnnotationId, $subProjectId, $userId),
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

    private function resolveTaskType(int $subProjectId): AnnotationTaskTypeEnum {
        $subProject = $this->subProjectByIdQuery->getWithProjectAndAnnotationTask($subProjectId);
        $project = $subProject->project ?? throw new RuntimeException(sprintf('SubProject %d has no parent project.', $subProjectId));
        $annotationTask = $project->annotationTask ?? throw new RuntimeException(sprintf('Project %d has no annotation task.', $project->id));

        return $annotationTask->task_type;
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

    /** @return array{can_navigate: bool, can_submit_all_pending: bool} */
    private function getSubProjectSettings(int $subProjectId): array {
        $subProject = $this->subProjectByIdQuery->get($subProjectId);

        return [
            'can_navigate' => $subProject->flexible,
            'can_submit_all_pending' => ! $subProject->auto_submission,
        ];
    }

    /** @return array<string, mixed> */
    private function getAnnotationProgressData(int $subProjectId, bool $canNavigate, int $userId, ?int $annotationSessionId): array {
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

        if ($canNavigate) {
            $data['pending_count'] = $stats->pendingCount;
            $data['submitted_and_pending_pct'] = $stats->submittedAndPendingPct;
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function getAnnotationTaskData(int $nextAnnotationId, int $subProjectId, int $userId): array {
        $annotation = $this->annotationByIdQuery->get($nextAnnotationId);
        $taskType = $this->resolveTaskType($subProjectId);
        $taskService = $this->taskServiceFactory->make($taskType);

        return [
            'annotator_instance_index' => $annotation->annotator_instance_index,
            'annotationData' => $this->getAnnotationData($nextAnnotationId, $userId),
            ...$taskService->getTaskRelatedData($annotation->dataset_instance_id, $subProjectId),
        ];
    }

    /** @return array{is_flagged: bool, flag_notification_thread_id: int|null, is_replied: bool|null, is_reply_read: bool|null, annotations: array<string, mixed>|null, confidence: string|null} */
    private function getAnnotationData(int $annotationId, int $userId): array {
        $annotation = $this->annotationByIdQuery->getAnnotationData($annotationId);

        $flagThreadId = $annotation->flag_notification_thread_id;
        $isReplied = null;
        $isReplyRead = null;

        if ($flagThreadId !== null) {
            $status = $this->flagThreadStatusQuery->get($flagThreadId, $userId);
            $isReplied = $status['is_replied'];
            $isReplyRead = $status['is_reply_read'];
        }

        return [
            'is_flagged' => $annotation->isFlagged(),
            'flag_notification_thread_id' => $flagThreadId,
            'is_replied' => $isReplied,
            'is_reply_read' => $isReplyRead,
            'annotations' => $annotation->annotations,
            'confidence' => $annotation->confidence?->value,
        ];
    }
}
