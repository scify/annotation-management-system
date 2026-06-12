<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\NotificationThreadTypeEnum;
use App\Models\Notification;
use App\Models\NotificationThread;
use App\Models\QuickLink;
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
        return $this->getMyNotificationsQuery->get($userId)->map(function (NotificationThread $thread): NotificationThread {
            $thread->quickLinks->each(fn (QuickLink $link) => $link->makeHidden(['id', 'notification_thread_id', 'created_at', 'updated_at']));

            $thread->notifications->transform(function (Notification $notification): Notification {
                $notification->setAttribute('sender_username', $notification->sender?->username);
                $notification->setAttribute('sender_role', $notification->sender?->role);
                $notification->setAttribute('date', $notification->created_at->toDateString());
                $notification->unsetRelation('sender');
                $notification->makeHidden(['created_at', 'updated_at']);

                return $notification;
            });

            return $thread;
        });
    }
}
