<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\NotificationThreadResponse;

final readonly class GetNotificationThreadSenderIdQuery {
    public function get(int $notificationThreadId): ?int {
        /** @var int|null */
        return NotificationThreadResponse::query()
            ->where('notification_thread_id', $notificationThreadId)
            ->value('sender_user_id');
    }
}
