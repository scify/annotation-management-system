<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\AnnotationAssignment;
use Illuminate\Support\Collection;

final readonly class GetAnnotatorSubprojectCountsQuery {
    /**
     * @param  array<int, mixed>  $annotatorIds
     * @param  Collection<int, mixed>  $subProjectIds
     *
     * @return Collection<int|string, mixed>
     */
    public function get(array $annotatorIds, Collection $subProjectIds): Collection {
        return AnnotationAssignment::query()
            ->whereIn('user_id', $annotatorIds)
            ->whereIn('sub_project_id', $subProjectIds)
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->pluck('count', 'user_id');
    }
}
