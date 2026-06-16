<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\ThreadMember;

final readonly class MarkNotificationAsReadQuery {
    public function mark(int $notificationId, int $userId): void {
        $member = ThreadMember::query()
            ->where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($member === null || $member->is_read) {
            return;
        }

        $member->update(['is_read' => true]);
    }
}
