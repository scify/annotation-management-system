<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\NotificationThreadTypeEnum;
use App\Models\NotificationThread;
use App\Queries\Notification\CreateNotificationQuery;
use App\Queries\Notification\CreateNotificationThreadQuery;
use App\Queries\Notification\CreateThreadMemberQuery;

final class InfoNotificationService extends AbstractNotificationService {
    public function __construct(
        private readonly CreateNotificationThreadQuery $createNotificationThreadQuery,
        private readonly CreateNotificationQuery $createNotificationQuery,
        private readonly CreateThreadMemberQuery $createThreadMemberQuery,
    ) {}

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
