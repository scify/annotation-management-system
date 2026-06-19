<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Data\QuickLinkData;
use App\Enums\NotificationThreadTypeEnum;
use App\Models\NotificationThread;
use App\Queries\Annotator\GetAnnotatorProjectLinksByProjectQuery;
use App\Queries\Notification\CreateNotificationQuery;
use App\Queries\Notification\CreateNotificationThreadQuery;
use App\Queries\Notification\CreateQuickLinkQuery;
use App\Queries\Notification\CreateThreadMemberQuery;
use App\Queries\Project\GetManagerIdsByProjectsQuery;
use App\Queries\Project\GetProjectBasicDataQuery;
use App\Queries\SubProject\GetAnnotatorIdsBySubProjectQuery;
use App\Queries\SubProject\GetSubProjectByIdQuery;

final class AnnouncementNotificationService extends AbstractNotificationService {
    public function __construct(
        private readonly CreateNotificationThreadQuery $createNotificationThreadQuery,
        private readonly CreateNotificationQuery $createNotificationQuery,
        private readonly CreateThreadMemberQuery $createThreadMemberQuery,
        private readonly CreateQuickLinkQuery $createQuickLinkQuery,
        private readonly GetAnnotatorProjectLinksByProjectQuery $getAnnotatorProjectLinksByProjectQuery,
        private readonly GetManagerIdsByProjectsQuery $getManagerIdsByProjectsQuery,
        private readonly GetAnnotatorIdsBySubProjectQuery $getAnnotatorIdsBySubProjectQuery,
        private readonly GetProjectBasicDataQuery $getProjectBasicDataQuery,
        private readonly GetSubProjectByIdQuery $getSubProjectByIdQuery,
    ) {}

    /**
     * Public entry point. Resolves recipients and the QuickLink automatically from the given IDs,
     * then dispatches to the appropriate announcement creator.
     * The QuickLink always points to the parent project page — there is no dedicated subproject show route.
     */
    public function notifyProjectMembers(
        int $projectId,
        ?int $subProjectId,
        string $body,
        int $senderUserId,
    ): null {
        if ($subProjectId !== null) {
            $subProject = $this->getSubProjectByIdQuery->get($subProjectId);
            $quickLink = new QuickLinkData(
                label: $subProject->name,
                url: route('projects.show', $projectId),
            );

            return $this->createSubProjectAnnouncement($subProjectId, $projectId, $body, $senderUserId, $quickLink);
        }

        $project = $this->getProjectBasicDataQuery->get($projectId);
        $quickLink = new QuickLinkData(
            label: $project['name'],
            url: route('projects.show', $projectId),
        );

        return $this->createProjectAnnouncement($projectId, $body, $senderUserId, $quickLink);
    }

    /**
     * Recipients are all users directly assigned to the project: annotators (annotator_of_project) and managers (project_managers).
     *
     * @param  int[]  $recipientUserIds
     */
    public function createNotification(
        array $recipientUserIds,
        string $body,
        int $senderUserId,
        QuickLinkData $quickLink,
    ): null {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::ANNOUNCEMENT);

        $this->createNotificationQuery->create(
            notificationThreadId: $thread->id,
            body: $body,
            senderUserId: $senderUserId,
        );

        // Announcements are one-way; only recipients are thread members.
        $this->createThreadMemberQuery->createBatch($thread->id, $recipientUserIds, false);
        $this->createQuickLinkQuery->create($thread->id, $quickLink->label, $quickLink->url);

        return null;
    }

    protected function setTitle(NotificationThread $thread, int $userId): void {
        $thread->setAttribute('title', $thread->notifications->first()?->sender?->username);
    }

    protected function setTopRight(NotificationThread $thread): void {
        $thread->setAttribute('top_right', $thread->quickLinks->first()?->label);
    }

    protected function allowsReply(): bool {
        return false;
    }

    /** Recipients = union of direct project annotators and project managers. */
    private function createProjectAnnouncement(int $projectId, string $body, int $senderUserId, QuickLinkData $quickLink): null {
        $annotatorIds = $this->getAnnotatorProjectLinksByProjectQuery->getUserIds($projectId);
        $managerIds = $this->getManagerIdsByProjectsQuery->getAll([$projectId]);
        $recipientIds = array_values(array_unique([...$annotatorIds, ...$managerIds]));

        return $this->createNotification(
            recipientUserIds: $recipientIds,
            body: $body,
            senderUserId: $senderUserId,
            quickLink: $quickLink,
        );
    }

    /** Recipients = annotators assigned to this specific subproject (AnnotationAssignment) + managers of the parent project. */
    private function createSubProjectAnnouncement(int $subProjectId, int $projectId, string $body, int $senderUserId, QuickLinkData $quickLink): null {
        $annotatorIds = $this->getAnnotatorIdsBySubProjectQuery->get($subProjectId);
        $managerIds = $this->getManagerIdsByProjectsQuery->getAll([$projectId]);
        $recipientIds = array_values(array_unique([...$annotatorIds, ...$managerIds]));

        return $this->createNotification(
            recipientUserIds: $recipientIds,
            body: $body,
            senderUserId: $senderUserId,
            quickLink: $quickLink,
        );
    }
}
