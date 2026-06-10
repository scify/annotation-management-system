<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Models\ProjectManager;

final readonly class RequestToLeaveQuery {
    public function execute(int $projectId, int $userId): void {
        ProjectManager::query()
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->update(['request_to_leave' => true]);
    }

    public function clear(int $projectId, int $userId): void {
        ProjectManager::query()
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->update(['request_to_leave' => false]);
    }
}
