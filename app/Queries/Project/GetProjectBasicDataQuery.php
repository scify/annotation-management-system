<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Models\Project;

final readonly class GetProjectBasicDataQuery {
    /**
     * @return array{project_id: int, name: string, owner_user_id: int}
     */
    public function get(int $projectId): array {
        $project = Project::query()
            ->select('id', 'name', 'owner_user_id')
            ->findOrFail($projectId);

        return [
            'project_id' => $project->id,
            'name' => $project->name,
            'owner_user_id' => $project->owner_user_id,
        ];
    }
}
