<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\ThreadMember;

final readonly class ExistsUnreadNotificationsQuery {
    public function exists(int $userId): bool {
        return ThreadMember::query()
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->exists();
    }
}
