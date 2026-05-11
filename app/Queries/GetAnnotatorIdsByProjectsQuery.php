<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\UserRelationsEnum;
use App\Models\UserRelation;

final readonly class GetAnnotatorIdsByProjectsQuery {
    /**
     * @param  array<int, mixed>  $projectIds
     *
     * @return array<int, mixed>
     */
    public function get(array $projectIds): array {
        return UserRelation::query()
            ->whereIn('project_id', $projectIds)
            ->where('relation_type', UserRelationsEnum::ANNOTATOR_OF_MANAGER)
            ->pluck('user_id')
            ->unique()
            ->all();
    }
}
