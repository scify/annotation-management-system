<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Data\TranslatableMessage;
use App\Enums\NotificationThreadTypeEnum;
use App\Models\NotificationThread;
use App\Queries\Notification\CreateNotificationQuery;
use App\Queries\Notification\CreateNotificationThreadQuery;
use App\Queries\Notification\CreateThreadMemberQuery;
use App\Queries\Project\GetManagerIdsByProjectsQuery;
use App\Queries\Project\GetProjectBasicDataQuery;
use App\Queries\User\GetUsernamesByIdsQuery;
use JsonException;

final class InfoNotificationService extends AbstractNotificationService {
    public function __construct(
        private readonly CreateNotificationThreadQuery $createNotificationThreadQuery,
        private readonly CreateNotificationQuery $createNotificationQuery,
        private readonly CreateThreadMemberQuery $createThreadMemberQuery,
        private readonly GetProjectBasicDataQuery $getProjectBasicDataQuery,
        private readonly GetManagerIdsByProjectsQuery $getManagerIdsByProjectsQuery,
        private readonly GetUsernamesByIdsQuery $getUsernamesByIdsQuery,
    ) {}

    /**
     * @param  array<int, int>  $annotatorIds
     *
     * @throws JsonException
     */
    public function notifyManagersAboutNewAnnotatorsOfProject(int $projectId, array $annotatorIds): void {
        $projectData = $this->getProjectBasicDataQuery->get($projectId);

        $names = implode(', ', $this->getUsernamesByIdsQuery->get($annotatorIds));

        $managerIds = $this->getManagerIdsByProjectsQuery->getAll([$projectId]);
        $recipientIds = array_unique([$projectData['owner_user_id'], ...$managerIds]);

        $body = TranslatableMessage::encode('notifications.messages.annotators_added_to_project.body', [
            'names' => $names,
            'project' => $projectData['name'],
        ]);

        $title = TranslatableMessage::encode('notifications.messages.annotators_added_to_project.title');

        foreach ($recipientIds as $recipientId) {
            $this->createNotification($recipientId, $body, $title);
        }
    }

    /**
     * @throws JsonException
     */
    public function notifyCancelledOwnershipProposal(int $projectId, int $proposedOwnerUserId, int $cancellerUserId): void {
        $projectData = $this->getProjectBasicDataQuery->get($projectId);
        $cancellerUsernames = $this->getUsernamesByIdsQuery->get([$cancellerUserId]);

        $this->createNotification(
            recipientUserId: $proposedOwnerUserId,
            body: TranslatableMessage::encode('notifications.messages.ownership_proposal_cancelled.body', [
                'project' => $projectData['name'],
                'sender' => $cancellerUsernames[0] ?? '',
            ]),
            title: TranslatableMessage::encode('notifications.messages.ownership_proposal_cancelled.title'),
        );
    }

    /**
     * @throws JsonException
     */
    public function notifyCancelledLeaveRequest(int $projectId, int $senderUserId): void {
        $projectData = $this->getProjectBasicDataQuery->get($projectId);
        $senderUsernames = $this->getUsernamesByIdsQuery->get([$senderUserId]);

        $this->createNotification(
            recipientUserId: $projectData['owner_user_id'],
            body: TranslatableMessage::encode('notifications.messages.leave_request_cancelled.body', [
                'project' => $projectData['name'],
                'sender' => $senderUsernames[0] ?? '',
            ]),
            title: TranslatableMessage::encode('notifications.messages.leave_request_cancelled.title'),
        );
    }

    /**
     * @throws JsonException
     */
    public function notifyLeaveRequestAccepted(int $projectId, int $memberUserId): void {
        $projectData = $this->getProjectBasicDataQuery->get($projectId);
        $usernames = $this->getUsernamesByIdsQuery->get([$memberUserId]);

        $this->createNotification(
            recipientUserId: $memberUserId,
            body: TranslatableMessage::encode('notifications.messages.leave_request_accepted.body', ['project' => $projectData['name'], 'recipient' => $usernames[0] ?? '']),
            title: TranslatableMessage::encode('notifications.messages.leave_request_accepted.title'),
        );
    }

    /**
     * @throws JsonException
     */
    public function notifyLeaveRequestRejected(int $projectId, int $memberUserId): void {
        $projectData = $this->getProjectBasicDataQuery->get($projectId);
        $usernames = $this->getUsernamesByIdsQuery->get([$memberUserId]);

        $this->createNotification(
            recipientUserId: $memberUserId,
            body: TranslatableMessage::encode('notifications.messages.leave_request_rejected.body', ['project' => $projectData['name'], 'recipient' => $usernames[0] ?? '']),
            title: TranslatableMessage::encode('notifications.messages.leave_request_rejected.title'),
        );
    }

    /**
     * @throws JsonException
     */
    public function notifyOwnerOfAcceptedOwnership(int $projectId, int $oldOwnerUserId, int $acceptingUserId): void {
        $projectData = $this->getProjectBasicDataQuery->get($projectId);
        $usernames = $this->getUsernamesByIdsQuery->get([$acceptingUserId]);

        $this->createNotification(
            recipientUserId: $oldOwnerUserId,
            body: TranslatableMessage::encode('notifications.messages.ownership_transfer_accepted.body', [
                'username' => $usernames[0] ?? '',
                'project' => $projectData['name'],
            ]),
            title: TranslatableMessage::encode('notifications.messages.ownership_transfer_accepted.title'),
        );
    }

    /**
     * @throws JsonException
     */
    public function notifyOwnerOfRejectedOwnership(int $projectId, int $rejectingUserId): void {
        $projectData = $this->getProjectBasicDataQuery->get($projectId);
        $usernames = $this->getUsernamesByIdsQuery->get([$rejectingUserId]);

        $this->createNotification(
            recipientUserId: $projectData['owner_user_id'],
            body: TranslatableMessage::encode('notifications.messages.ownership_transfer_rejected.body', [
                'username' => $usernames[0] ?? '',
                'project' => $projectData['name'],
            ]),
            title: TranslatableMessage::encode('notifications.messages.ownership_transfer_rejected.title'),
        );
    }

    /**
     * @throws JsonException
     */
    public function notifyRemovedManager(int $projectId, int $removedUserId): void {
        $projectData = $this->getProjectBasicDataQuery->get($projectId);
        $usernames = $this->getUsernamesByIdsQuery->get([$removedUserId]);

        $this->createNotification(
            recipientUserId: $removedUserId,
            body: TranslatableMessage::encode('notifications.messages.manager_removed_from_project.body', ['project' => $projectData['name'], 'recipient' => $usernames[0] ?? '']),
            title: TranslatableMessage::encode('notifications.messages.manager_removed_from_project.title'),
        );
    }

    public function createNotification(
        int $recipientUserId,
        string $body,
        string $title,
    ): null {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::INFO, $title);

        $this->createNotificationQuery->create(
            notificationThreadId: $thread->id,
            body: $body,
        );

        $this->createThreadMemberQuery->create($thread->id, $recipientUserId);

        return null;
    }

    protected function setTitle(NotificationThread $thread, int $userId): void {
        // Title is stored in the thread's title column on creation; no dynamic override needed.
    }

    protected function setTopRight(NotificationThread $thread): void {
        $thread->setAttribute('top_right', null);
    }

    protected function allowsReply(): bool {
        return false;
    }
}
