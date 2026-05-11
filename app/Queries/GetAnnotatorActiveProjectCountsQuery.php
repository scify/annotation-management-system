<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Enums\UserRelationsEnum;
use App\Models\UserRelation;
use Illuminate\Support\Collection;

final readonly class GetAnnotatorActiveProjectCountsQuery {
    /**
     * @param  array<int, mixed>  $annotatorIds
     *
     * @return Collection<int|string, mixed>
     */
    public function get(array $annotatorIds): Collection {
        return UserRelation::query()
            ->whereIn('user_relations.user_id', $annotatorIds)
            ->where('user_relations.relation_type', UserRelationsEnum::ANNOTATOR_OF_MANAGER)
            ->join('projects', 'projects.id', '=', 'user_relations.project_id')
            ->where('projects.status', ProjectStatusEnum::IN_PROGRESS)
            ->selectRaw('user_relations.user_id, COUNT(*) as count')
            ->groupBy('user_relations.user_id')
            ->pluck('count', 'user_relations.user_id');
    }
}
