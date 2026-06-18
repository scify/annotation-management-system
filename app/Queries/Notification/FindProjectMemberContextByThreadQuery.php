<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Data\ProjectMemberContextData;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ThreadMember;

final readonly class FindProjectMemberContextByThreadQuery {
    public function find(int $notificationThreadId): ?ProjectMemberContextData {
        $senderUserId = Notification::query()
            ->where('notification_thread_id', $notificationThreadId)
            ->orderBy('id')
            ->first()
            ?->sender_user_id;

        if ($senderUserId === null) {
            return null;
        }

        $targetUserId = ThreadMember::query()
            ->where('notification_thread_id', $notificationThreadId)
            ->where('user_id', '!=', $senderUserId)
            ->first()
            ?->user_id;

        if ($targetUserId === null) {
            return null;
        }

        $projectId = Project::query()
            ->where('owner_user_id', $senderUserId)
            ->first()
            ?->id;

        if ($projectId === null) {
            return null;
        }

        return new ProjectMemberContextData($projectId, $targetUserId);
    }
}
