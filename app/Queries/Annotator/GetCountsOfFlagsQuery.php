<?php

declare(strict_types=1);

namespace App\Queries\Annotator;

use App\Models\Annotation;

final readonly class GetCountsOfFlagsQuery {
    /**
     * Returns flagged annotation counts grouped by user and sub-project.
     *
     * @param  array<int, mixed>  $annotatorIds
     *
     * @return array<int, array<int, int>> [userId][subProjectId] => flagCount
     */
    public function get(array $annotatorIds): array {
        if ($annotatorIds === []) {
            return [];
        }

        /** @var array<int, array{user_id: int|string, sub_project_id: int|string, flag_count: int|string}> $rows */
        $rows = Annotation::query()
            ->join('annotation_assignments', 'annotation_assignments.id', '=', 'annotations.annotation_assignment_id')
            ->whereIn('annotation_assignments.user_id', $annotatorIds)
            ->whereNotNull('annotations.flag_notification_thread_id')
            ->whereNull('annotations.annotations')
            ->selectRaw('annotation_assignments.user_id, annotation_assignments.sub_project_id, COUNT(*) as flag_count')
            ->groupBy('annotation_assignments.user_id', 'annotation_assignments.sub_project_id')
            ->get()
            ->toArray();

        $result = [];

        foreach ($rows as $row) {
            $result[(int) $row['user_id']][(int) $row['sub_project_id']] = (int) $row['flag_count'];
        }

        return $result;
    }
}
