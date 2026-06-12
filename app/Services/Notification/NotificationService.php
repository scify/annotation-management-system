<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\NotificationThreadTypeEnum;
use App\Models\Notification;
use App\Models\NotificationThread;
use App\Queries\Notification\CreateNotificationQuery;
use App\Queries\Notification\CreateNotificationThreadQuery;
use App\Queries\Notification\GetMyNotificationsQuery;
use App\Queries\Notification\MarkNotificationAsReadQuery;
use Illuminate\Support\Collection;

readonly class NotificationService {
    public function __construct(
        private CreateNotificationThreadQuery $createNotificationThreadQuery,
        private CreateNotificationQuery $createNotificationQuery,
        private MarkNotificationAsReadQuery $markNotificationAsReadQuery,
        private GetMyNotificationsQuery $getMyNotificationsQuery,
    ) {}

    public function createGenericNotification(
        int $recipientUserId,
        string $body,
        ?int $senderUserId = null,
    ): Notification {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::GENERIC);

        return $this->createNotificationQuery->create(
            notificationThreadId: $thread->id,
            recipientUserId: $recipientUserId,
            body: $body,
            senderUserId: $senderUserId,
        );
    }

    public function replyToGenericNotification(
        int $notificationThreadId,
        int $recipientUserId,
        string $body,
        ?int $senderUserId = null,
    ): Notification {
        return $this->createNotificationQuery->create(
            notificationThreadId: $notificationThreadId,
            recipientUserId: $recipientUserId,
            body: $body,
            senderUserId: $senderUserId,
        );
    }

    public function markAsRead(Notification $notification): void {
        $this->markNotificationAsReadQuery->mark($notification);
    }

    /**
     * @return Collection<int, NotificationThread>
     */
    public function getMyNotifications(int $userId): Collection {
        return $this->getMyNotificationsQuery->get($userId);
    }
}
