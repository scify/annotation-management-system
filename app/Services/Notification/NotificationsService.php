<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\NotificationThreadTypeEnum;
use App\Models\Notification;
use App\Models\NotificationThread;
use App\Models\QuickLink;
use App\Queries\Notification\ExistsUnreadNotificationsQuery;
use App\Queries\Notification\GetMyNotificationsQuery;
use App\Queries\Notification\GetThreadRecipientsQuery;
use App\Queries\Notification\MarkAllThreadsReadStatusQuery;
use App\Queries\Notification\MarkThreadReadStatusQuery;
use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class NotificationsService {
    public function __construct(
        private GenericNotificationService $genericService,
        private WarningNotificationService $warningService,
        private InfoNotificationService $infoService,
        private FlagNotificationService $flagService,
        private InstanceRelatedNotificationService $instanceRelatedService,
        private AnnouncementNotificationService $announcementService,
        private ProjectOwnershipNotificationService $projectOwnershipService,
        private ProjectInvitationNotificationService $projectInvitationService,
        private MarkThreadReadStatusQuery $markThreadReadStatusQuery,
        private MarkAllThreadsReadStatusQuery $markAllThreadsReadStatusQuery,
        private GetMyNotificationsQuery $getMyNotificationsQuery,
        private GetThreadRecipientsQuery $getThreadRecipientsQuery,
        private ExistsUnreadNotificationsQuery $existsUnreadNotificationsQuery,
    ) {}

    /** Marks a specific notification thread as read for the given user. */
    public function markAsRead(int $notificationThreadId, int $userId): void {
        $this->markThreadReadStatusQuery->mark($notificationThreadId, $userId, true);
    }

    /** Marks a specific notification thread as unread for the given user. */
    public function markAsUnread(int $notificationThreadId, int $userId): void {
        $this->markThreadReadStatusQuery->mark($notificationThreadId, $userId, false);
    }

    /** Marks all notification threads the user belongs to as read. */
    public function markAllAsRead(int $userId): void {
        $this->markAllThreadsReadStatusQuery->markAll($userId, true);
    }

    /** Returns true if the user has at least one unread notification thread. */
    public function hasUnreadNotifications(int $userId): bool {
        return $this->existsUnreadNotificationsQuery->exists($userId);
    }

    /**
     * Fetches all notification threads for the user and enriches each with computed
     * display attributes — read status, datetime, replied_by, and type-specific
     * title/top_right/response/allowed_to_reply via the per-type service.
     *
     * @return Collection<int, NotificationThread>
     */
    public function getMyNotifications(int $userId): Collection {
        return $this->getMyNotificationsQuery->get($userId)->map(function (NotificationThread $thread) use ($userId): NotificationThread {
            $thread->quickLinks->each(fn (QuickLink $link) => $link->makeHidden(['id', 'notification_thread_id', 'created_at', 'updated_at']));

            /** @var Carbon|null $latestAt */
            $latestAt = $thread->notifications->max('created_at');
            $thread->setAttribute('datetime', $latestAt?->toDateTimeString() ?? '');

            $userMembers = $thread->members->where('user_id', $userId);
            $thread->setAttribute('is_read', $userMembers->every(fn ($m) => $m->is_read));

            $lastNotification = $thread->notifications->last();
            $thread->setAttribute(
                'replied_by',
                $thread->notifications->count() === 1 ? null : $lastNotification?->sender?->username,
            );

            $this->resolveService($thread->type)->augmentNotification($thread, $userId);

            $senderUserId = $thread->notifications->first()?->sender_user_id;
            $thread->setAttribute('recipients', $this->getThreadRecipientsQuery->get($thread->id, $senderUserId));
            $thread->unsetRelation('members');

            $thread->notifications->transform(function (Notification $notification): Notification {
                $notification->setAttribute('sender_username', $notification->sender?->username);
                $notification->setAttribute('sender_role', $notification->sender?->role);
                $notification->setAttribute('datetime', $notification->created_at->toDateTimeString());
                $notification->unsetRelation('sender');
                $notification->makeHidden(['created_at', 'updated_at']);

                return $notification;
            });

            return $thread;
        })->sortByDesc('datetime')->values();
    }

    /** Dispatches a thread type to its corresponding per-type notification service. */
    private function resolveService(NotificationThreadTypeEnum $type): AbstractNotificationService {
        return match ($type) {
            NotificationThreadTypeEnum::GENERIC => $this->genericService,
            NotificationThreadTypeEnum::WARNING => $this->warningService,
            NotificationThreadTypeEnum::INFO => $this->infoService,
            NotificationThreadTypeEnum::FLAG_NOTIFICATION => $this->flagService,
            NotificationThreadTypeEnum::INSTANCE_RELATED => $this->instanceRelatedService,
            NotificationThreadTypeEnum::ANNOUNCEMENT => $this->announcementService,
            NotificationThreadTypeEnum::PROJECT_OWNERSHIP => $this->projectOwnershipService,
            NotificationThreadTypeEnum::PROJECT_INVITATION => $this->projectInvitationService,
        };
    }
}
