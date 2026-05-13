<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\AnnotatorOfManager;
use App\Models\Comanager;
use App\Models\Project;

final readonly class GetAnnotatorIdsByProjectsQuery {
    /**
     * @param  array<int, mixed>  $projectIds
     *
     * @return array<int, mixed>
     */
    public function get(array $projectIds): array {
        if ($projectIds === []) {
            return [];
        }

        $ownerIds = Project::query()->whereIn('id', $projectIds)->pluck('owner_user_id');
        $comanagerIds = Comanager::query()->whereIn('project_id', $projectIds)->pluck('user_id');

        $managerIds = $ownerIds->merge($comanagerIds)->unique()->values()->all();

        if ($managerIds === []) {
            return [];
        }

        return AnnotatorOfManager::query()
            ->whereIn('manager_id', $managerIds)
            ->pluck('annotator_id')
            ->unique()
            ->all();
    }
}
