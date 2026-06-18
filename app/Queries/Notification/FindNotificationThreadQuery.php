<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\NotificationThread;

class FindNotificationThreadQuery {
    public function findOrFail(int $id): NotificationThread {
        return NotificationThread::query()->findOrFail($id);
    }
}
