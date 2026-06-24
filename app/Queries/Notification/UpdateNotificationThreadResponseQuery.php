<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Enums\NotificationThreadResponseEnum;
use App\Enums\NotificationThreadTypeEnum;
use App\Models\NotificationThread;
use App\Models\NotificationThreadResponse;

class UpdateNotificationThreadResponseQuery {
    public function update(int $notificationThreadId, NotificationThreadResponseEnum $response): void {
        NotificationThreadResponse::query()
            ->where('notification_thread_id', $notificationThreadId)
            ->update(['response' => $response]);
    }

    public function cancelForOwnershipTransfer(int $projectId, int $userId): void {
        NotificationThreadResponse::query()
            ->whereIn('notification_thread_id', NotificationThread::query()
                ->select('id')
                ->where('project_id', $projectId)
                ->where('type', NotificationThreadTypeEnum::PROJECT_OWNERSHIP)
            )
            ->where('recipient_user_id', $userId)
            ->where('response', NotificationThreadResponseEnum::UNREPLIED)
            ->update(['response' => NotificationThreadResponseEnum::CANCELED]);
    }

    public function cancelForRequestToLeave(int $projectId, int $userId): void {
        NotificationThreadResponse::query()
            ->whereIn('notification_thread_id', NotificationThread::query()
                ->select('id')
                ->where('project_id', $projectId)
                ->where('type', NotificationThreadTypeEnum::PROJECT_REQUEST_TO_LEAVE)
            )
            ->where('sender_user_id', $userId)
            ->where('response', NotificationThreadResponseEnum::UNREPLIED)
            ->update(['response' => NotificationThreadResponseEnum::CANCELED]);
    }
}
