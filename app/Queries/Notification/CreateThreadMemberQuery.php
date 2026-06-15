<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\ThreadMember;

final readonly class CreateThreadMemberQuery {
    public function create(int $notificationId, int $userId, bool $isRead = false): ThreadMember {
        /** @var ThreadMember */
        return ThreadMember::query()->create([
            'notification_id' => $notificationId,
            'user_id' => $userId,
            'is_read' => $isRead,
        ]);
    }
}
