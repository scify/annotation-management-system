<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Data\ProjectMemberContextData;
use App\Models\NotificationThread;

final readonly class FindProjectMemberContextByThreadQuery {
    public function find(int $notificationThreadId): ?ProjectMemberContextData {
        $thread = NotificationThread::query()->find($notificationThreadId);

        if ($thread === null || $thread->project_id === null || $thread->target_user_id === null) {
            return null;
        }

        return new ProjectMemberContextData($thread->project_id, $thread->target_user_id);
    }
}
