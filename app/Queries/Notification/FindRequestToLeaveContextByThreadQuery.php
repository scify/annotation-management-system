<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Data\ProjectMemberContextData;
use App\Models\Notification;
use App\Models\ProjectManager;

final readonly class FindRequestToLeaveContextByThreadQuery {
    public function find(int $notificationThreadId): ?ProjectMemberContextData {
        $senderUserId = Notification::query()
            ->where('notification_thread_id', $notificationThreadId)
            ->orderBy('id')
            ->first()
            ?->sender_user_id;

        if ($senderUserId === null) {
            return null;
        }

        $projectId = ProjectManager::query()
            ->where('user_id', $senderUserId)
            ->where('request_to_leave', true)
            ->first()
            ?->project_id;

        if ($projectId === null) {
            return null;
        }

        return new ProjectMemberContextData($projectId, $senderUserId);
    }
}
