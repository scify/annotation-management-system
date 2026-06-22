<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\Notification;

final readonly class GetNotificationThreadSenderIdQuery {
    public function get(int $notificationThreadId): ?int {
        /** @var int|null */
        return Notification::query()
            ->where('notification_thread_id', $notificationThreadId)
            ->orderBy('id')
            ->value('sender_user_id');
    }
}
