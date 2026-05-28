<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ConfidenceEnum;
use App\Models\Annotation;
use App\Models\User;

final readonly class GetAnnotationsBySubProjectQuery {
    /**
     * Returns per-dataset-instance annotation counts, raw values, and metadata for a given sub-project.
     *
     * @return array<int, array{
     *     annotated: int,
     *     planned_annotations: int,
     *     annotations_values: array<int, array{annotations: array<string, mixed>|null, pending: bool}>,
     *     annotations: array<int, array{id: int, annotator_data: array{user_id: int, username: string, role: string|null}, last_edited_by_data: array{user_id: int, username: string|null, role: string|null}|null, updated_at: string|null, confidence: ConfidenceEnum|null, pending: bool}>
     * }>
     */
    public function get(int $subProjectId): array {
        /** @var array<int, array{id: int, dataset_instance_id: int|string, annotations: array<string, mixed>|null, pending: bool, updated_at: string, confidence: ConfidenceEnum|null, user_id: int|string, username: string, role: string|null, last_edited_by: int|string|null, editor_username: string|null, editor_role: string|null}> $rows */
        $rows = Annotation::query()
            ->join('annotation_assignments', 'annotation_assignments.id', '=', 'annotations.annotation_assignment_id')
            ->join('users', 'users.id', '=', 'annotation_assignments.user_id')
            ->leftJoin('model_has_roles', function ($join): void {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', '=', User::class);
            })
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoin('users as editor_users', 'editor_users.id', '=', 'annotations.last_edited_by')
            ->leftJoin('model_has_roles as editor_model_has_roles', function ($join): void {
                $join->on('editor_model_has_roles.model_id', '=', 'editor_users.id')
                    ->where('editor_model_has_roles.model_type', '=', User::class);
            })
            ->leftJoin('roles as editor_roles', 'editor_roles.id', '=', 'editor_model_has_roles.role_id')
            ->where('annotation_assignments.sub_project_id', $subProjectId)
            ->select(
                'annotations.id',
                'annotations.dataset_instance_id',
                'annotations.annotations',
                'annotations.pending',
                'annotations.updated_at',
                'annotations.confidence',
                'annotations.last_edited_by',
                'annotation_assignments.user_id',
                'users.username',
                'roles.name as role',
                'editor_users.username as editor_username',
                'editor_roles.name as editor_role',
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

            $hasEditor = $row['last_edited_by'] !== null;

            $entry['annotations'][] = [
                'id' => $row['id'],
                'annotator_data' => [
                    'user_id' => (int) $row['user_id'],
                    'username' => $row['username'],
                    'role' => $row['role'],
                ],
                'last_edited_by_data' => $hasEditor ? [
                    'user_id' => (int) $row['last_edited_by'],
                    'username' => $row['editor_username'],
                    'role' => $row['editor_role'],
                ] : null,
                'updated_at' => $hasEditor ? $row['updated_at'] : null,
                'confidence' => $row['confidence'],
                'pending' => $row['pending'],
            ];

            $result[$dsId] = $entry;
        }

        return $result;
    }
}
