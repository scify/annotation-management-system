<?php

declare(strict_types=1);

namespace App\Queries\SubProject;

use App\Models\AnnotationAssignment;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetAssignmentsBySubProjectsQuery {
    /**
     * @param  array<int, int>  $subProjectIds
     *
     * @return Collection<int, AnnotationAssignment>
     */
    public function get(array $subProjectIds): Collection {
        if ($subProjectIds === []) {
            return new Collection();
        }

        return AnnotationAssignment::query()
            ->whereIn('sub_project_id', $subProjectIds)
            ->select('id', 'sub_project_id', 'user_id')
            ->get();
    }
}
