<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Data\QuickLinkData;
use App\Enums\NotificationThreadTypeEnum;
use App\Models\Notification;
use App\Models\NotificationThread;
use App\Queries\Notification\CreateNotificationQuery;
use App\Queries\Notification\CreateNotificationThreadQuery;
use App\Queries\Notification\CreateQuickLinkQuery;
use App\Queries\Notification\CreateThreadMemberQuery;

final class FlagNotificationService extends AbstractNotificationService {
    public function __construct(
        private readonly CreateNotificationThreadQuery $createNotificationThreadQuery,
        private readonly CreateNotificationQuery $createNotificationQuery,
        private readonly CreateThreadMemberQuery $createThreadMemberQuery,
        private readonly CreateQuickLinkQuery $createQuickLinkQuery,
    ) {}

    /**
     * @param  int[]  $recipientUserIds
     */
    public function createNotification(
        array $recipientUserIds,
        string $body,
        int $senderUserId,
        QuickLinkData $firstQuickLink,
        QuickLinkData $secondQuickLink,
    ): Notification {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::FLAG_NOTIFICATION);

        $notification = $this->createNotificationQuery->create(
            notificationThreadId: $thread->id,
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->createThreadMemberQuery->create($thread->id, $senderUserId, true);
        $this->createThreadMemberQuery->createBatch($thread->id, $recipientUserIds);

        $this->createQuickLinkQuery->create($thread->id, $firstQuickLink->label, $firstQuickLink->url);
        $this->createQuickLinkQuery->create($thread->id, $secondQuickLink->label, $secondQuickLink->url);

        return $notification;
    }

    protected function setTitle(NotificationThread $thread, int $userId): void {
        $thread->setAttribute('title', $thread->notifications->first()?->sender?->username);
    }

    protected function setTopRight(NotificationThread $thread): void {
        $thread->setAttribute('top_right', $thread->quickLinks->first()?->label);
    }

    protected function allowsReply(): bool {
        return true;
    }
}
