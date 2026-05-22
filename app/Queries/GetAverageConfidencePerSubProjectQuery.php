<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Annotation;

final readonly class GetAverageConfidencePerSubProjectQuery {
    /**
     * Returns the numeric average confidence (low=0.1, medium=0.5, high=1.0)
     * per annotator per sub-project. Sub-projects with no confidence rows are omitted.
     *
     * @param  array<int, mixed>  $annotatorIds
     *
     * @return array<int, array<int, float>> [userId][subProjectId] => numericAverage
     */
    public function get(array $annotatorIds): array {
        if ($annotatorIds === []) {
            return [];
        }

        /** @var array<int, array{user_id: int|string, sub_project_id: int|string, avg_confidence: float|string|null}> $rows */
        $rows = Annotation::query()
            ->join('confidences', 'confidences.annotation_id', '=', 'annotations.id')
            ->join('annotation_assignments', 'annotation_assignments.id', '=', 'annotations.annotation_assignment_id')
            ->whereIn('annotation_assignments.user_id', $annotatorIds)
            ->selectRaw("
                annotation_assignments.user_id,
                annotation_assignments.sub_project_id,
                AVG(CASE confidences.value
                    WHEN 'low'    THEN 0.1
                    WHEN 'medium' THEN 0.5
                    WHEN 'high'   THEN 1.0
                    ELSE NULL
                END) as avg_confidence
            ")
            ->groupBy('annotation_assignments.user_id', 'annotation_assignments.sub_project_id')
            ->get()
            ->toArray();

        $result = [];

        foreach ($rows as $row) {
            if ($row['avg_confidence'] === null) {
                continue;
            }

            $result[(int) $row['user_id']][(int) $row['sub_project_id']] = (float) $row['avg_confidence'];
        }

        return $result;
    }
}
