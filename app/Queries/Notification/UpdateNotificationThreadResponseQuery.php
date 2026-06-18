<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Enums\NotificationThreadResponseEnum;
use App\Models\NotificationThreadResponse;

class UpdateNotificationThreadResponseQuery {
    public function update(int $notificationThreadId, NotificationThreadResponseEnum $response): void {
        NotificationThreadResponse::query()
            ->where('notification_thread_id', $notificationThreadId)
            ->update(['response' => $response]);
    }
}
