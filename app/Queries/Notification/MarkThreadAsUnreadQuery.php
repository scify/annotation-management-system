<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\ThreadMember;

final readonly class MarkThreadAsUnreadQuery {
    public function mark(int $notificationThreadId, int $userId): void {
        ThreadMember::query()
            ->whereHas('notification', fn ($q) => $q->where('notification_thread_id', $notificationThreadId))
            ->where('user_id', $userId)
            ->update(['is_read' => false]);
    }
}
