<?php

declare(strict_types=1);

namespace App\Queries\Manager;

use App\Models\ProjectManager;

final readonly class ConnectManagerToProjectsQuery {
    /**
     * @param  array<int, int>  $projectIds
     */
    public function connect(int $managerId, array $projectIds): void {
        foreach ($projectIds as $projectId) {
            ProjectManager::query()->create([
                'project_id' => $projectId,
                'user_id' => $managerId,
                'accepted' => true,
            ]);
        }
    }
}
