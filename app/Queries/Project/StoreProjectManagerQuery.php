<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Models\ProjectManager;

final readonly class StoreProjectManagerQuery {
    public function create(int $projectId, int $userId, bool $accepted = true): void {
        ProjectManager::query()->create([
            'project_id' => $projectId,
            'user_id' => $userId,
            'accepted' => $accepted,
        ]);
    }

    public function firstOrCreate(int $projectId, int $userId): void {
        ProjectManager::query()->firstOrCreate(
            ['project_id' => $projectId, 'user_id' => $userId],
            ['accepted' => true],
        );
    }
}
