<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\ThreadMember;

final readonly class UpdateThreadMembersQuery {
    public function update(int $notificationThreadId, int $senderUserId): void {
        ThreadMember::query()
            ->where('notification_thread_id', $notificationThreadId)
            ->where('user_id', '!=', $senderUserId)
            ->update(['is_read' => false]);

        ThreadMember::query()
            ->where('notification_thread_id', $notificationThreadId)
            ->where('user_id', $senderUserId)
            ->update(['is_read' => true]);
    }
}
