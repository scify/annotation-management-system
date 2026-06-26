<?php

declare(strict_types=1);

namespace App\Queries\SubProject;

use App\Models\Annotation;

final readonly class GetAnnotationCountsBySubProjectsQuery {
    /**
     * Returns pending, submitted, and not-annotated row counts per sub-project.
     * When $userId is provided, counts are scoped to that annotator only.
     *
     * @param  array<int, int>  $subProjectIds
     *
     * @return array<int, array{pending_count: int, submitted_count: int, not_annotated_count: int}>
     */
    public function get(array $subProjectIds, ?int $userId = null): array {
        if ($subProjectIds === []) {
            return [];
        }

        /** @var array<int, array{sub_project_id: int|string, pending_count: int|string, submitted_count: int|string, not_annotated_count: int|string}> $rows */
        $rows = Annotation::query()
            ->join('annotation_assignments', 'annotation_assignments.id', '=', 'annotations.annotation_assignment_id')
            ->whereIn('annotation_assignments.sub_project_id', $subProjectIds)
            ->when($userId !== null, fn ($q) => $q->where('annotation_assignments.user_id', $userId))
            ->selectRaw('
                annotation_assignments.sub_project_id,
                SUM(CASE WHEN annotations.pending = 1 THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN annotations.pending = 0 AND annotations.annotations IS NOT NULL THEN 1 ELSE 0 END) as submitted_count,
                SUM(CASE WHEN annotations.pending = 0 AND annotations.annotations IS NULL THEN 1 ELSE 0 END) as not_annotated_count
            ')
            ->groupBy('annotation_assignments.sub_project_id')
            ->get()
            ->toArray();

        $result = [];

        foreach ($rows as $row) {
            $result[(int) $row['sub_project_id']] = [
                'pending_count' => (int) $row['pending_count'],
                'submitted_count' => (int) $row['submitted_count'],
                'not_annotated_count' => (int) $row['not_annotated_count'],
            ];
        }

        return $result;
    }
}
