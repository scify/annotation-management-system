<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Data\QuickLinkData;
use App\Enums\NotificationThreadTypeEnum;
use App\Models\Notification;
use App\Models\NotificationThread;
use App\Queries\Notification\CreateNotificationQuery;
use App\Queries\Notification\CreateNotificationThreadQuery;
use App\Queries\Notification\CreateNotificationThreadResponseQuery;
use App\Queries\Notification\CreateQuickLinkQuery;
use App\Queries\Notification\CreateThreadMemberQuery;

final class ProjectInvitationNotificationService extends AbstractNotificationService {
    public function __construct(
        private readonly CreateNotificationThreadQuery $createNotificationThreadQuery,
        private readonly CreateNotificationQuery $createNotificationQuery,
        private readonly CreateThreadMemberQuery $createThreadMemberQuery,
        private readonly CreateNotificationThreadResponseQuery $createNotificationThreadResponseQuery,
        private readonly CreateQuickLinkQuery $createQuickLinkQuery,
    ) {}

    public function createNotification(
        int $recipientUserId,
        int $senderUserId,
        string $body,
        QuickLinkData $quickLink,
    ): Notification {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::PROJECT_INVITATION);

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

    protected function setResponse(NotificationThread $thread): void {
        $thread->setAttribute('response', $thread->response?->response->value);
        $thread->unsetRelation('response');
    }

    protected function setTitle(NotificationThread $thread, int $userId): void {
        $thread->setAttribute('title', $thread->notifications->first()?->sender?->username);
    }

    protected function setTopRight(NotificationThread $thread): void {
        $thread->setAttribute('top_right', 'Invitation to Project');
    }

    protected function allowsReply(): bool {
        return false;
    }
}
