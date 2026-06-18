<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Data\ProjectMemberContextData;
use App\Data\QuickLinkData;
use App\Enums\NotificationThreadResponseEnum;
use App\Enums\NotificationThreadTypeEnum;
use App\Exceptions\NotificationResponseException;
use App\Models\Notification;
use App\Models\NotificationThread;
use App\Models\NotificationThreadResponse;
use App\Queries\Notification\CreateNotificationQuery;
use App\Queries\Notification\CreateNotificationThreadQuery;
use App\Queries\Notification\CreateNotificationThreadResponseQuery;
use App\Queries\Notification\CreateQuickLinkQuery;
use App\Queries\Notification\CreateThreadMemberQuery;
use App\Queries\Notification\FindNotificationThreadResponseQuery;
use App\Queries\Notification\FindProjectMemberContextByThreadQuery;
use App\Queries\Notification\UpdateNotificationThreadResponseQuery;
use App\Queries\Project\GetProjectBasicDataQuery;
use App\Queries\Project\IsProposedOwnerQuery;
use App\Services\Project\ProjectManagerService;

final class ProjectOwnershipNotificationService extends AbstractNotificationService {
    public function __construct(
        private readonly CreateNotificationThreadQuery $createNotificationThreadQuery,
        private readonly CreateNotificationQuery $createNotificationQuery,
        private readonly CreateThreadMemberQuery $createThreadMemberQuery,
        private readonly CreateNotificationThreadResponseQuery $createNotificationThreadResponseQuery,
        private readonly CreateQuickLinkQuery $createQuickLinkQuery,
        private readonly UpdateNotificationThreadResponseQuery $updateNotificationThreadResponseQuery,
        private readonly FindNotificationThreadResponseQuery $findNotificationThreadResponseQuery,
        private readonly FindProjectMemberContextByThreadQuery $findProjectMemberContextByThreadQuery,
        private readonly ProjectManagerService $projectManagerService,
        private readonly GetProjectBasicDataQuery $getProjectBasicDataQuery,
        private readonly IsProposedOwnerQuery $isProposedOwnerQuery,
    ) {}

    public function notifyProposedOwner(int $projectId, int $senderUserId, int $recipientUserId): void {
        $projectData = $this->getProjectBasicDataQuery->get($projectId);

        $this->createNotification(
            recipientUserId: $recipientUserId,
            senderUserId: $senderUserId,
            body: (string) __('notifications.messages.project_ownership', ['project' => $projectData['name']]),
            quickLink: new QuickLinkData(
                label: $projectData['name'],
                url: route('projects.show', $projectId),
            ),
        );
    }

    public function createNotification(
        int $recipientUserId,
        int $senderUserId,
        string $body,
        QuickLinkData $quickLink,
    ): Notification {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::PROJECT_OWNERSHIP);

        $notification = $this->createNotificationQuery->create(
            notificationThreadId: $thread->id,
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->createThreadMemberQuery->create($thread->id, $senderUserId, true);
        $this->createThreadMemberQuery->create($thread->id, $recipientUserId);

        $this->createNotificationThreadResponseQuery->create($thread->id);
        $this->createQuickLinkQuery->create($thread->id, $quickLink->label, $quickLink->url);

        return $notification;
    }

    public function approve(int $notificationThreadId): void {
        $response = $this->findNotificationThreadResponseQuery->find($notificationThreadId);

        if (! $response instanceof NotificationThreadResponse) {
            throw NotificationResponseException::responseNotFound();
        }

        if ($response->response === NotificationThreadResponseEnum::CANCELED) {
            throw NotificationResponseException::cannotRespondCancelled();
        }

        if ($response->response === NotificationThreadResponseEnum::REJECTED) {
            throw NotificationResponseException::cannotApproveRejected();
        }

        $memberContext = $this->findProjectMemberContextByThreadQuery->find($notificationThreadId);

        if ($memberContext instanceof ProjectMemberContextData
            && ! $this->isProposedOwnerQuery->check($memberContext->projectId, $memberContext->targetUserId)) {
            $this->updateNotificationThreadResponseQuery->update($notificationThreadId, NotificationThreadResponseEnum::CANCELED);

            throw NotificationResponseException::cannotRespondCancelled();
        }

        $this->updateNotificationThreadResponseQuery->update($notificationThreadId, NotificationThreadResponseEnum::ACCEPTED);

        if ($memberContext instanceof ProjectMemberContextData) {
            $this->projectManagerService->acceptOwnershipTransfer($memberContext->projectId, $memberContext->targetUserId);
        }
    }

    public function reject(int $notificationThreadId): void {
        $response = $this->findNotificationThreadResponseQuery->find($notificationThreadId);

        if (! $response instanceof NotificationThreadResponse) {
            throw NotificationResponseException::responseNotFound();
        }

        if ($response->response === NotificationThreadResponseEnum::CANCELED) {
            throw NotificationResponseException::cannotRespondCancelled();
        }

        if ($response->response === NotificationThreadResponseEnum::ACCEPTED) {
            throw NotificationResponseException::cannotRejectAccepted();
        }

        $memberContext = $this->findProjectMemberContextByThreadQuery->find($notificationThreadId);

        if ($memberContext instanceof ProjectMemberContextData
            && ! $this->isProposedOwnerQuery->check($memberContext->projectId, $memberContext->targetUserId)) {
            $this->updateNotificationThreadResponseQuery->update($notificationThreadId, NotificationThreadResponseEnum::CANCELED);

            throw NotificationResponseException::cannotRespondCancelled();
        }

        $this->updateNotificationThreadResponseQuery->update($notificationThreadId, NotificationThreadResponseEnum::REJECTED);

        if ($memberContext instanceof ProjectMemberContextData) {
            $this->projectManagerService->rejectOwnershipTransfer($memberContext->projectId, $memberContext->targetUserId);
        }
    }

    protected function setResponse(NotificationThread $thread): void {
        $thread->setAttribute('response', $thread->response?->response->value);
        $thread->unsetRelation('response');
    }

    protected function setTitle(NotificationThread $thread, int $userId): void {
        $thread->setAttribute('title', $thread->notifications->first()?->sender?->username);
    }

    protected function setTopRight(NotificationThread $thread): void {
        $thread->setAttribute('top_right', 'Ownership');
    }

    protected function allowsReply(): bool {
        return false;
    }
}
