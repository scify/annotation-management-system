<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Data\QuickLinkData;
use App\Enums\NotificationThreadTypeEnum;
use App\Models\Notification;
use App\Models\NotificationThread;
use App\Models\QuickLink;
use App\Queries\Notification\CreateNotificationQuery;
use App\Queries\Notification\CreateNotificationThreadQuery;
use App\Queries\Notification\CreateQuickLinkQuery;
use App\Queries\Notification\GetMyNotificationsQuery;
use App\Queries\Notification\MarkNotificationAsReadQuery;
use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class NotificationService {
    public function __construct(
        private CreateNotificationThreadQuery $createNotificationThreadQuery,
        private CreateNotificationQuery $createNotificationQuery,
        private CreateQuickLinkQuery $createQuickLinkQuery,
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

    /**
     * @param  int[]  $recipientUserIds
     */
    public function createWarningNotification(
        array $recipientUserIds,
        string $title,
        string $body,
    ): void {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::WARNING, $title);

        foreach ($recipientUserIds as $recipientUserId) {
            $this->createNotificationQuery->create(
                notificationThreadId: $thread->id,
                recipientUserId: $recipientUserId,
                body: $body,
            );
        }
    }

    /**
     * @param  int[]  $recipientUserIds
     */
    public function createInfoNotification(
        array $recipientUserIds,
        string $title,
        string $body,
    ): void {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::INFO, $title);

        foreach ($recipientUserIds as $recipientUserId) {
            $this->createNotificationQuery->create(
                notificationThreadId: $thread->id,
                recipientUserId: $recipientUserId,
                body: $body,
            );
        }
    }

    public function createFlagNotification(
        int $recipientUserId,
        int $senderUserId,
        string $body,
        QuickLinkData $firstQuickLink,
        QuickLinkData $secondQuickLink,
    ): Notification {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::FLAG_NOTIFICATION);

        $notification = $this->createNotificationQuery->create(
            notificationThreadId: $thread->id,
            recipientUserId: $recipientUserId,
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->createQuickLinkQuery->create($thread->id, $firstQuickLink->label, $firstQuickLink->url);
        $this->createQuickLinkQuery->create($thread->id, $secondQuickLink->label, $secondQuickLink->url);

        return $notification;
    }

    public function createInstanceRelatedNotification(
        int $recipientUserId,
        int $senderUserId,
        string $body,
        QuickLinkData $firstQuickLink,
        QuickLinkData $secondQuickLink,
    ): Notification {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::INSTANCE_RELATED);

        $notification = $this->createNotificationQuery->create(
            notificationThreadId: $thread->id,
            recipientUserId: $recipientUserId,
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->createQuickLinkQuery->create($thread->id, $firstQuickLink->label, $firstQuickLink->url);
        $this->createQuickLinkQuery->create($thread->id, $secondQuickLink->label, $secondQuickLink->url);

        return $notification;
    }

    /**
     * @param  int[]  $recipientUserIds
     */
    public function createAnnouncementNotification(
        array $recipientUserIds,
        int $senderUserId,
        string $body,
        QuickLinkData $quickLink,
    ): void {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::ANNOUNCEMENT);

        foreach ($recipientUserIds as $recipientUserId) {
            $this->createNotificationQuery->create(
                notificationThreadId: $thread->id,
                recipientUserId: $recipientUserId,
                body: $body,
                senderUserId: $senderUserId,
            );
        }

        $this->createQuickLinkQuery->create($thread->id, $quickLink->label, $quickLink->url);
    }

    public function createProjectOwnershipNotification(
        int $recipientUserId,
        int $senderUserId,
        string $body,
        QuickLinkData $quickLink,
    ): Notification {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::PROJECT_OWNERSHIP);

        $notification = $this->createNotificationQuery->create(
            notificationThreadId: $thread->id,
            recipientUserId: $recipientUserId,
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->createQuickLinkQuery->create($thread->id, $quickLink->label, $quickLink->url);

        return $notification;
    }

    public function createProjectInvitationNotification(
        int $recipientUserId,
        int $senderUserId,
        string $body,
        QuickLinkData $quickLink,
    ): Notification {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::PROJECT_INVITATION);

        $notification = $this->createNotificationQuery->create(
            notificationThreadId: $thread->id,
            recipientUserId: $recipientUserId,
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->createQuickLinkQuery->create($thread->id, $quickLink->label, $quickLink->url);

        return $notification;
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
        return $this->getMyNotificationsQuery->get($userId)->map(function (NotificationThread $thread) use ($userId): NotificationThread {
            $thread->quickLinks->each(fn (QuickLink $link) => $link->makeHidden(['id', 'notification_thread_id', 'created_at', 'updated_at']));

            /** @var Carbon|null $latestAt */
            $latestAt = $thread->notifications->max('created_at');
            $thread->setAttribute('datetime', $latestAt?->toDateTimeString() ?? '');

            $thread->setAttribute(
                'is_read',
                $thread->notifications
                    ->where('recipient_user_id', $userId)
                    ->every(fn (Notification $n): bool => $n->is_read),
            );

            $thread->setAttribute('top_right', match ($thread->type) {
                NotificationThreadTypeEnum::FLAG_NOTIFICATION,
                NotificationThreadTypeEnum::INSTANCE_RELATED,
                NotificationThreadTypeEnum::ANNOUNCEMENT => $thread->quickLinks->first()?->label,
                NotificationThreadTypeEnum::PROJECT_OWNERSHIP => 'Ownership',
                NotificationThreadTypeEnum::PROJECT_INVITATION => 'Invitation to Project',
                default => null,
            });

            if ($thread->type === NotificationThreadTypeEnum::GENERIC) {
                $first = $thread->notifications->first();
                $thread->setAttribute(
                    'title',
                    $first?->sender_user_id !== null && $first->sender_user_id !== $userId
                        ? $first->sender?->username
                        : $first?->recipient?->username,
                );
            }

            $thread->notifications->transform(function (Notification $notification): Notification {
                $notification->setAttribute('sender_username', $notification->sender?->username);
                $notification->setAttribute('sender_role', $notification->sender?->role);
                $notification->setAttribute('datetime', $notification->created_at->toDateTimeString());
                $notification->unsetRelation('sender');
                $notification->unsetRelation('recipient');
                $notification->makeHidden(['created_at', 'updated_at']);

                return $notification;
            });

            return $thread;
        })->sortByDesc('datetime')->values();
    }
}
