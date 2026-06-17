<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\ThreadMember;

final readonly class MarkThreadReadStatusQuery {
    public function mark(int $notificationThreadId, int $userId, bool $isRead): void {
        ThreadMember::query()
            ->where('notification_thread_id', $notificationThreadId)
            ->where('user_id', $userId)
            ->update(['is_read' => $isRead]);
    }
}
