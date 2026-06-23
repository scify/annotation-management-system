<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Data\ProjectMemberContextData;
use App\Models\NotificationThread;

final readonly class FindProjectMemberContextByThreadQuery {
    public function find(int $notificationThreadId): ?ProjectMemberContextData {
        $thread = NotificationThread::query()->with('response')->find($notificationThreadId);

        if ($thread === null || $thread->project_id === null || $thread->response?->recipient_user_id === null) {
            return null;
        }

        return new ProjectMemberContextData($thread->project_id, $thread->response->recipient_user_id);
    }
}
