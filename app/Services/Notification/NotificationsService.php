<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\NotificationThreadTypeEnum;
use App\Models\Notification;
use App\Models\NotificationThread;
use App\Models\QuickLink;
use App\Models\User;
use App\Queries\Notification\ExistsUnreadNotificationsQuery;
use App\Queries\Notification\FindNotificationThreadQuery;
use App\Queries\Notification\GetMyNotificationsQuery;
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
        private ProjectRequestToLeaveNotificationService $projectRequestToLeaveService,
        private MarkThreadReadStatusQuery $markThreadReadStatusQuery,
        private MarkAllThreadsReadStatusQuery $markAllThreadsReadStatusQuery,
        private GetMyNotificationsQuery $getMyNotificationsQuery,
        private ExistsUnreadNotificationsQuery $existsUnreadNotificationsQuery,
        private FindNotificationThreadQuery $findNotificationThreadQuery,
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

    public function approve(int $notificationThreadId): void {
        $thread = $this->findNotificationThreadQuery->findOrFail($notificationThreadId);

        match ($thread->type) {
            NotificationThreadTypeEnum::PROJECT_OWNERSHIP => $this->projectOwnershipService->approve($notificationThreadId),
            NotificationThreadTypeEnum::PROJECT_INVITATION => $this->projectInvitationService->approve($notificationThreadId),
            NotificationThreadTypeEnum::PROJECT_REQUEST_TO_LEAVE => $this->projectRequestToLeaveService->approve($notificationThreadId),
            default => abort(403),
        };
    }

    public function reject(int $notificationThreadId): void {
        $thread = $this->findNotificationThreadQuery->findOrFail($notificationThreadId);

        match ($thread->type) {
            NotificationThreadTypeEnum::PROJECT_OWNERSHIP => $this->projectOwnershipService->reject($notificationThreadId),
            NotificationThreadTypeEnum::PROJECT_INVITATION => $this->projectInvitationService->reject($notificationThreadId),
            NotificationThreadTypeEnum::PROJECT_REQUEST_TO_LEAVE => $this->projectRequestToLeaveService->reject($notificationThreadId),
            default => abort(403),
        };
    }

    public function reply(int $notificationThreadId, int $senderUserId, string $body): Notification {
        $thread = $this->findNotificationThreadQuery->findOrFail($notificationThreadId);

        return match ($thread->type) {
            NotificationThreadTypeEnum::GENERIC => $this->genericService->reply($notificationThreadId, $senderUserId, $body),
            NotificationThreadTypeEnum::FLAG_NOTIFICATION => $this->flagService->reply($notificationThreadId, $senderUserId, $body),
            NotificationThreadTypeEnum::INSTANCE_RELATED => $this->instanceRelatedService->reply($notificationThreadId, $senderUserId, $body),
            default => abort(403),
        };
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
    public function getMyNotifications(User $user): Collection {
        $userId = $user->id;

        return $this->getMyNotificationsQuery->get($userId)->map(function (NotificationThread $thread) use ($user, $userId): NotificationThread {
            $thread->quickLinks->each(function (QuickLink $link) use ($user): void {
                $link->setAttribute('label', $link->getLabel($user));
                $link->makeHidden(['id', 'notification_thread_id', 'annotation_id', 'created_at', 'updated_at']);
            });

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
            $thread->setAttribute('recipients', $thread->members
                ->when($senderUserId !== null, fn ($c) => $c->filter(fn ($m): bool => $m->user_id !== $senderUserId))
                ->map(fn ($m) => $m->user->username)
                ->filter()
                ->unique()
                ->values()
                ->all()
            );
            $thread->unsetRelation('members');

            $thread->notifications->transform(fn (Notification $notification): Notification => $this->presentNotification($notification));

            return $thread;
        })->sortByDesc('datetime')->values();
    }

    /**
     * Appends the display attributes (sender username/role, formatted datetime) the
     * frontend `NotificationMessage` expects, and strips the relation and timestamps.
     * Shared by getMyNotifications() and the single-message reply response.
     */
    public function presentNotification(Notification $notification): Notification {
        $notification->loadMissing('sender');
        $notification->setAttribute('sender_username', $notification->sender?->username);
        $notification->setAttribute('sender_role', $notification->sender?->role);
        $notification->setAttribute('datetime', $notification->created_at->toDateTimeString());
        $notification->unsetRelation('sender');
        $notification->makeHidden(['created_at', 'updated_at']);

        return $notification;
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
            NotificationThreadTypeEnum::PROJECT_REQUEST_TO_LEAVE => $this->projectRequestToLeaveService,
        };
    }
}
