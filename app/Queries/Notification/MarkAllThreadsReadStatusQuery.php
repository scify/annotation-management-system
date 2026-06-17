<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\ThreadMember;

final readonly class MarkAllThreadsReadStatusQuery {
    public function markAll(int $userId, bool $isRead): void {
        ThreadMember::query()
            ->where('user_id', $userId)
            ->update(['is_read' => $isRead]);
    }
}
