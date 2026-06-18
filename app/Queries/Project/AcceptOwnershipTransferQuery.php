<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Models\Project;
use App\Models\ProjectManager;

final readonly class AcceptOwnershipTransferQuery {
    public function getOwnerUserId(int $projectId): int {
        return Project::query()->findOrFail($projectId)->owner_user_id;
    }

    public function clearProposal(int $projectId, int $userId): void {
        ProjectManager::query()
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->update(['proposed_to_become_owner' => false]);
    }

    public function transferOwner(int $projectId, int $userId): void {
        Project::query()
            ->where('id', $projectId)
            ->update(['owner_user_id' => $userId]);
    }
}
