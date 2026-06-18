<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\NotificationThreadResponse;

class FindNotificationThreadResponseQuery {
    public function find(int $notificationThreadId): ?NotificationThreadResponse {
        return NotificationThreadResponse::query()
            ->where('notification_thread_id', $notificationThreadId)
            ->first();
    }
}
