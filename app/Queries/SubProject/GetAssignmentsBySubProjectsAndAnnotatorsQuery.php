<?php

declare(strict_types=1);

namespace App\Queries\SubProject;

use App\Models\AnnotationAssignment;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetAssignmentsBySubProjectsAndAnnotatorsQuery {
    /**
     * @param  array<int, mixed>  $subProjectIds
     * @param  array<int, mixed>  $annotatorIds
     *
     * @return Collection<int, AnnotationAssignment>
     */
    public function get(array $subProjectIds, array $annotatorIds): Collection {
        if ($subProjectIds === [] || $annotatorIds === []) {
            return new Collection();
        }

        return AnnotationAssignment::query()
            ->whereIn('sub_project_id', $subProjectIds)
            ->whereIn('user_id', $annotatorIds)
            ->get();
    }

    /**
     * @param  array<int, int>  $subProjectIds
     */
    public function existsBySubProjectsAndAnnotator(array $subProjectIds, int $annotatorId): bool {
        if ($subProjectIds === []) {
            return false;
        }

        return AnnotationAssignment::query()
            ->whereIn('sub_project_id', $subProjectIds)
            ->where('user_id', $annotatorId)
            ->exists();
    }
}
