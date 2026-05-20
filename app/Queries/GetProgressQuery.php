<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Annotation;

final readonly class GetProgressQuery {
    /**
     * @param  array<int, mixed>  $annotationAssignmentIds
     *
     * @return array<int, array{annotations_done: int, annotations_all: int, progress: float}>
     */
    public function get(array $annotationAssignmentIds): array {
        if ($annotationAssignmentIds === []) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $rows */
        $rows = Annotation::query()
            ->whereIn('annotation_assignment_id', $annotationAssignmentIds)
            ->selectRaw('
                annotation_assignment_id,
                COUNT(*) as annotations_all,
                SUM(CASE WHEN annotations IS NOT NULL AND pending = 0 THEN 1 ELSE 0 END) as annotations_done
            ')
            ->groupBy('annotation_assignment_id')
            ->get()
            ->toArray();

        $result = [];

        foreach ($rows as $row) {
            $all = (int) $row['annotations_all'];
            $done = (int) $row['annotations_done'];

            $result[(int) $row['annotation_assignment_id']] = [
                'annotations_all' => $all,
                'annotations_done' => $done,
                'progress' => $all > 0 ? $done / $all : 0.0,
            ];
        }

        return $result;
    }
}
