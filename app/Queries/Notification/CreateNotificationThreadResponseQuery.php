<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Enums\NotificationThreadResponseEnum;
use App\Models\NotificationThreadResponse;

final readonly class CreateNotificationThreadResponseQuery {
    public function create(int $notificationThreadId, int $senderUserId, int $recipientUserId): NotificationThreadResponse {
        /** @var NotificationThreadResponse */
        return NotificationThreadResponse::query()->create([
            'notification_thread_id' => $notificationThreadId,
            'response' => NotificationThreadResponseEnum::UNREPLIED,
            'sender_user_id' => $senderUserId,
            'recipient_user_id' => $recipientUserId,
        ]);
    }
}
