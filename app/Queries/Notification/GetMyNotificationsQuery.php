<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\NotificationThread;
use App\Models\ThreadMember;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final readonly class GetMyNotificationsQuery {
    /**
     * Returns all NotificationThreads where the given user is a member, with all
     * thread notifications, their senders, thread members with users, and quick links loaded.
     * Threads are sorted ascending by the oldest notification created_at.
     */
    /** @return Collection<int, NotificationThread> */
    public function get(int $userId): Collection {
        return NotificationThread::query()
            ->select(['id', 'type', 'title'])
            ->whereIn('id', ThreadMember::query()->select('notification_thread_id')->where('user_id', $userId))
            ->with([
                'notifications' => fn ($q) => $q
                    ->select(['id', 'notification_thread_id', 'sender_user_id', 'body', 'created_at'])
                    ->with(['sender']),
                'members.user',
                'quickLinks',
                'response',
            ])
            ->get()
            ->sortBy(function (NotificationThread $thread): string {
                /** @var Carbon|null $min */
                $min = $thread->notifications->min('created_at');

                return $min?->toDateTimeString() ?? '';
            })
            ->values();
    }
}
