<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Models\ProjectManager;

final readonly class ProposeOwnershipTransferQuery {
    public function hasActiveProposal(int $projectId): bool {
        return ProjectManager::query()
            ->where('project_id', $projectId)
            ->where('proposed_to_become_owner', true)
            ->exists();
    }

    public function execute(int $projectId, int $userId): void {
        ProjectManager::query()
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->update(['proposed_to_become_owner' => true]);
    }
}
