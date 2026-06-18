<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Models\ProjectManager;

final readonly class IsProposedOwnerQuery {
    public function check(int $projectId, int $userId): bool {
        return ProjectManager::query()
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->where('proposed_to_become_owner', true)
            ->exists();
    }
}
