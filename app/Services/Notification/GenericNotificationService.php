<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\NotificationThreadTypeEnum;
use App\Models\Notification;
use App\Models\NotificationThread;
use App\Queries\Notification\CreateNotificationQuery;
use App\Queries\Notification\CreateNotificationThreadQuery;
use App\Queries\Notification\CreateThreadMemberQuery;
use App\Queries\Notification\UpdateThreadMembersQuery;

final class GenericNotificationService extends AbstractNotificationService {
    public function __construct(
        private readonly CreateNotificationThreadQuery $createNotificationThreadQuery,
        private readonly CreateNotificationQuery $createNotificationQuery,
        private readonly CreateThreadMemberQuery $createThreadMemberQuery,
        private readonly UpdateThreadMembersQuery $updateThreadMemberQuery,
    ) {}

    public function createNotification(
        int $recipientUserId,
        string $body,
        int $senderUserId,
    ): Notification {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::GENERIC);

        $notification = $this->createNotificationQuery->create(
            notificationThreadId: $thread->id,
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->createThreadMemberQuery->create($thread->id, $senderUserId, true);
        $this->createThreadMemberQuery->create($thread->id, $recipientUserId);

        return $notification;
    }

    public function reply(
        int $notificationThreadId,
        int $senderUserId,
        string $body,
    ): Notification {
        $notification = $this->createNotificationQuery->create(
            notificationThreadId: $notificationThreadId,
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->updateThreadMemberQuery->update($notificationThreadId, $senderUserId);

        return $notification;
    }

    protected function setTitle(NotificationThread $thread, int $userId): void {
        $thread->setAttribute('title', $thread->notifications->first()?->sender?->username);
    }

    protected function setTopRight(NotificationThread $thread): void {
        $thread->setAttribute('top_right', null);
    }

    protected function allowsReply(): bool {
        return true;
    }
}
