<?php

declare(strict_types=1);

namespace App\Queries\Manager;

use App\Models\ProjectManager;

final readonly class SyncProjectsForManagerQuery {
    /**
     * @param  array<int, int>  $projectIds
     */
    public function sync(int $managerId, array $projectIds): void {
        ProjectManager::query()->where('user_id', $managerId)->delete();

        foreach ($projectIds as $projectId) {
            ProjectManager::query()->create([
                'project_id' => $projectId,
                'user_id' => $managerId,
                'accepted' => true,
            ]);
        }
    }
}
