<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Models\Project;

final readonly class GetDatasetIdsByProjectIdsQuery {
    /**
     * @param  array<int, int>  $projectIds
     *
     * @return array<int, int>
     */
    public function get(array $projectIds): array {
        if ($projectIds === []) {
            return [];
        }

        /** @var array<int, int> */
        return Project::query()
            ->whereIn('id', $projectIds)
            ->pluck('dataset_id')
            ->all();
    }
}
