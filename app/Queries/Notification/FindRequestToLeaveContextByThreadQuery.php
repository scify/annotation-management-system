<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Data\ProjectMemberContextData;
use App\Models\Notification;
use App\Models\ProjectManager;
use App\Models\ThreadMember;

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

        $recipientUserId = ThreadMember::query()
            ->where('notification_thread_id', $notificationThreadId)
            ->where('user_id', '!=', $senderUserId)
            ->first()
            ?->user_id;

        if ($recipientUserId === null) {
            return null;
        }

        $projectId = ProjectManager::query()
            ->where('user_id', $recipientUserId)
            ->where('request_to_leave', true)
            ->first()
            ?->project_id;

        if ($projectId === null) {
            return null;
        }

        return new ProjectMemberContextData($projectId, $recipientUserId);
    }
}
