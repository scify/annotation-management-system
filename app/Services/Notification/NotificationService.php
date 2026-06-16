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
use App\Queries\Notification\CreateNotificationThreadResponseQuery;
use App\Queries\Notification\CreateQuickLinkQuery;
use App\Queries\Notification\CreateThreadMemberQuery;
use App\Queries\Notification\GetMyNotificationsQuery;
use App\Queries\Notification\MarkNotificationAsReadQuery;
use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class NotificationService {
    public function __construct(
        private CreateNotificationThreadQuery $createNotificationThreadQuery,
        private CreateNotificationQuery $createNotificationQuery,
        private CreateQuickLinkQuery $createQuickLinkQuery,
        private CreateThreadMemberQuery $createThreadMemberQuery,
        private CreateNotificationThreadResponseQuery $createNotificationThreadResponseQuery,
        private MarkNotificationAsReadQuery $markNotificationAsReadQuery,
        private GetMyNotificationsQuery $getMyNotificationsQuery,
    ) {}

    public function createGenericNotification(
        int $recipientUserId,
        string $body,
        ?int $senderUserId = null,
    ): Notification {
        $thread = $this->createNotificationThreadQuery->create(NotificationThreadTypeEnum::GENERIC);

        $notification = $this->createNotificationQuery->create(
            notificationThreadId: $thread->id,
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->createThreadMembers($notification, $recipientUserId, $senderUserId);

        return $notification;
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
            $notification = $this->createNotificationQuery->create(
                notificationThreadId: $thread->id,
                body: $body,
            );

            $this->createThreadMemberQuery->create($notification->id, $recipientUserId, false);
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
            $notification = $this->createNotificationQuery->create(
                notificationThreadId: $thread->id,
                body: $body,
            );

            $this->createThreadMemberQuery->create($notification->id, $recipientUserId, false);
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
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->createThreadMembers($notification, $recipientUserId, $senderUserId);

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
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->createThreadMembers($notification, $recipientUserId, $senderUserId);

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
            $notification = $this->createNotificationQuery->create(
                notificationThreadId: $thread->id,
                body: $body,
                senderUserId: $senderUserId,
            );

            $this->createThreadMembers($notification, $recipientUserId, $senderUserId);
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
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->createThreadMembers($notification, $recipientUserId, $senderUserId);
        $this->createNotificationThreadResponseQuery->create($thread->id);

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
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->createThreadMembers($notification, $recipientUserId, $senderUserId);
        $this->createNotificationThreadResponseQuery->create($thread->id);

        $this->createQuickLinkQuery->create($thread->id, $quickLink->label, $quickLink->url);

        return $notification;
    }

    public function replyToGenericNotification(
        int $notificationThreadId,
        int $recipientUserId,
        string $body,
        ?int $senderUserId = null,
    ): Notification {
        $notification = $this->createNotificationQuery->create(
            notificationThreadId: $notificationThreadId,
            body: $body,
            senderUserId: $senderUserId,
        );

        $this->createThreadMembers($notification, $recipientUserId, $senderUserId);

        return $notification;
    }

    public function markAsRead(Notification $notification, int $userId): void {
        $this->markNotificationAsReadQuery->mark($notification, $userId);
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

            $userMembers = $thread->notifications->flatMap(fn (Notification $n) => $n->members->where('user_id', $userId));

            $thread->setAttribute('is_read', $userMembers->every(fn ($m) => $m->is_read));

            $thread->setAttribute('allowed_to_reply', match ($thread->type) {
                NotificationThreadTypeEnum::GENERIC,
                NotificationThreadTypeEnum::FLAG_NOTIFICATION,
                NotificationThreadTypeEnum::INSTANCE_RELATED => true,
                default => false,
            });

            $lastNotification = $thread->notifications->last();
            $thread->setAttribute(
                'replied_by',
                $thread->notifications->count() === 1 ? null : $lastNotification?->sender?->username,
            );

            if (in_array($thread->type, [NotificationThreadTypeEnum::PROJECT_OWNERSHIP, NotificationThreadTypeEnum::PROJECT_INVITATION], true)) {
                $thread->setAttribute('response', $thread->response?->response->value);
            }

            $thread->unsetRelation('response');

            $thread->setAttribute('top_right', match ($thread->type) {
                NotificationThreadTypeEnum::FLAG_NOTIFICATION,
                NotificationThreadTypeEnum::INSTANCE_RELATED,
                NotificationThreadTypeEnum::ANNOUNCEMENT => $thread->quickLinks->first()?->label,
                NotificationThreadTypeEnum::PROJECT_OWNERSHIP => 'Ownership',
                NotificationThreadTypeEnum::PROJECT_INVITATION => 'Invitation to Project',
                default => null,
            });

            if (! in_array($thread->type, [NotificationThreadTypeEnum::WARNING, NotificationThreadTypeEnum::INFO], true)) {
                $first = $thread->notifications->first();
                if ($thread->type === NotificationThreadTypeEnum::GENERIC) {
                    $otherMember = $first?->members->firstWhere('user_id', '!=', $userId);
                    $thread->setAttribute(
                        'title',
                        $first?->sender_user_id !== null && $first->sender_user_id !== $userId
                            ? $first->sender?->username
                            : $otherMember?->user?->username,
                    );
                } else {
                    $thread->setAttribute('title', $first?->sender?->username);
                }
            }

            $thread->notifications->transform(function (Notification $notification): Notification {
                $notification->setAttribute('sender_username', $notification->sender?->username);
                $notification->setAttribute('sender_role', $notification->sender?->role);
                $notification->setAttribute('datetime', $notification->created_at->toDateTimeString());
                $notification->unsetRelation('sender');
                $notification->unsetRelation('members');
                $notification->makeHidden(['created_at', 'updated_at']);

                return $notification;
            });

            return $thread;
        })->sortByDesc('datetime')->values();
    }

    private function createThreadMembers(Notification $notification, int $recipientUserId, ?int $senderUserId): void {
        $this->createThreadMemberQuery->create($notification->id, $recipientUserId, false);

        if ($senderUserId !== null) {
            $this->createThreadMemberQuery->create($notification->id, $senderUserId, true);
        }
    }
}
