<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ConfidenceEnum;
use App\Models\Annotation;

final readonly class GetAnnotationsBySubProjectQuery {
    /**
     * Returns per-dataset-instance annotation counts, raw values, and metadata for a given sub-project.
     *
     * @return array<int, array{
     *     annotated: int,
     *     planned_annotations: int,
     *     annotations_values: array<int, array{annotations: array<string, mixed>|null, pending: bool}>,
     *     annotations: array<int, array{id: int, annotator_name: string, last_edited_by: string, updated_at: string, confidence: ConfidenceEnum|null}>
     * }>
     */
    public function get(int $subProjectId): array {
        /** @var array<int, array{id: int, dataset_instance_id: int|string, annotations: array<string, mixed>|null, pending: bool, updated_at: string, confidence: ConfidenceEnum|null, username: string}> $rows */
        $rows = Annotation::query()
            ->join('annotation_assignments', 'annotation_assignments.id', '=', 'annotations.annotation_assignment_id')
            ->join('users', 'users.id', '=', 'annotation_assignments.user_id')
            ->where('annotation_assignments.sub_project_id', $subProjectId)
            ->select(
                'annotations.id',
                'annotations.dataset_instance_id',
                'annotations.annotations',
                'annotations.pending',
                'annotations.updated_at',
                'annotations.confidence',
                'users.username',
            )
            ->get()
            ->toArray();

        $result = [];

        foreach ($rows as $row) {
            $dsId = (int) $row['dataset_instance_id'];
            $entry = $result[$dsId] ?? [
                'planned_annotations' => 0,
                'annotated' => 0,
                'annotations_values' => [],
                'annotations' => [],
            ];
            $entry['planned_annotations']++;

            if (! $row['pending']) {
                $entry['annotated']++;
            }

            $entry['annotations_values'][] = [
                'annotations' => $row['annotations'],
                'pending' => $row['pending'],
            ];

            $entry['annotations'][] = [
                'id' => $row['id'],
                'annotator_name' => $row['username'],
                'last_edited_by' => $row['username'],
                'updated_at' => $row['updated_at'],
                'confidence' => $row['confidence'],
            ];

            $result[$dsId] = $entry;
        }

        return $result;
    }
}
