<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\Notification;

final readonly class CreateNotificationQuery {
    public function create(
        int $notificationThreadId,
        string $body,
        ?int $senderUserId = null,
    ): Notification {
        /** @var Notification */
        return Notification::query()->create([
            'notification_thread_id' => $notificationThreadId,
            'sender_user_id' => $senderUserId,
            'body' => $body,
        ]);
    }
}
