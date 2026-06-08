<?php

declare(strict_types=1);

namespace App\Queries\Annotator;

use App\Enums\ProjectStatusEnum;
use App\Models\AnnotationAssignment;
use Illuminate\Support\Collection;

final readonly class GetCountsOfActiveProjectsPerAnnotatorQuery {
    /**
     * @param  array<int, mixed>  $annotatorIds
     *
     * @return Collection<int|string, string|int>
     */
    public function get(array $annotatorIds): Collection {
        /** @var Collection<int|string, string|int> $result */
        $result = AnnotationAssignment::query()
            ->whereIn('annotation_assignments.user_id', $annotatorIds)
            ->join('sub_projects', 'sub_projects.id', '=', 'annotation_assignments.sub_project_id')
            ->join('projects', 'projects.id', '=', 'sub_projects.project_id')
            ->where('projects.status', ProjectStatusEnum::IN_PROGRESS)
            ->selectRaw('annotation_assignments.user_id, COUNT(DISTINCT projects.id) as count')
            ->groupBy('annotation_assignments.user_id')
            ->pluck('count', 'annotation_assignments.user_id');

        return $result;
    }
}
