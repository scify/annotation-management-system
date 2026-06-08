<?php

declare(strict_types=1);

namespace App\Queries\Annotator;

use App\Models\AnnotatorOfManager;
use App\Models\ProjectManager;

final readonly class GetAnnotatorIdsByProjectsQuery {
    /**
     * @param  array<int, mixed>  $projectIds
     *
     * @return array<int, int>
     */
    public function get(array $projectIds): array {
        if ($projectIds === []) {
            return [];
        }

        $managerIds = ProjectManager::query()
            ->whereIn('project_id', $projectIds)
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();

        if ($managerIds === []) {
            return [];
        }

        /** @var array<int, int> $result */
        $result = AnnotatorOfManager::query()
            ->whereIn('manager_id', $managerIds)
            ->pluck('annotator_id')
            ->unique()
            ->all();

        return $result;
    }
}
