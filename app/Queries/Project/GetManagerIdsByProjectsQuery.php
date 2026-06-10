<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Models\ProjectManager;

final readonly class GetManagerIdsByProjectsQuery {
    /**
     * Returns unique user IDs of all project managers for the given project IDs, excluding one user.
     *
     * @param  array<int, int>  $projectIds
     *
     * @return array<int, int>
     */
    public function get(array $projectIds, int $excludeUserId): array {
        if ($projectIds === []) {
            return [];
        }

        /** @var array<int, int> */
        return ProjectManager::query()
            ->whereIn('project_id', $projectIds)
            ->pluck('user_id')
            ->unique()
            ->reject(fn (mixed $id): bool => is_numeric($id) && (int) $id === $excludeUserId)
            ->values()
            ->all();
    }
}
