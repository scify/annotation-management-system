<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\NotificationThread;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final readonly class GetMyNotificationsQuery {
    /**
     * Returns all NotificationThreads that contain at least one notification
     * addressed to the given user, with all thread notifications and quick links
     * loaded. Threads are sorted ascending by the oldest notification created_at.
     */
    public function get(int $userId): Collection {
        return NotificationThread::query()
            ->whereHas('notifications', fn ($q) => $q->where('recipient_user_id', $userId))
            ->with(['notifications', 'quickLinks'])
            ->get()
            ->sortBy(function (NotificationThread $thread): string {
                /** @var Carbon|null $min */
                $min = $thread->notifications->min('created_at');

                return $min?->toDateTimeString() ?? '';
            })
            ->values();
    }
}
