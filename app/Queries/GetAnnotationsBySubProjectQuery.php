<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\AnnotationStatusEnum;
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
     *     annotations: array<int, array{id: int, annotator_data: array{user_id: int, username: string, role: string|null}, last_edited_by_data: array{user_id: int, username: string|null, role: string|null}|null, updated_at: string|null, confidence: ConfidenceEnum|null, status: string}>
     * }>
     */
    public function get(int $subProjectId): array {
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
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $dsId = $row->dataset_instance_id;
            $entry = $result[$dsId] ?? [
                'planned_annotations' => 0,
                'annotated' => 0,
                'annotations_values' => [],
                'annotations' => [],
            ];
            $entry['planned_annotations']++;

            if ($row->isAnnotated()) {
                $entry['annotated']++;
            }

            $entry['annotations_values'][] = [
                'annotations' => $row->annotations,
                'pending' => $row->pending,
            ];

            $hasEditor = $row->last_edited_by !== null;

            /** @var int $userId */
            $userId = $row->getAttribute('user_id');
            /** @var string $username */
            $username = $row->getAttribute('username');
            /** @var string|null $role */
            $role = $row->getAttribute('role');
            /** @var string|null $editorUsername */
            $editorUsername = $row->getAttribute('editor_username');
            /** @var string|null $editorRole */
            $editorRole = $row->getAttribute('editor_role');

            $entry['annotations'][] = [
                'id' => $row->id,
                'annotator_data' => [
                    'user_id' => $userId,
                    'username' => $username,
                    'role' => $role,
                ],
                'last_edited_by_data' => $hasEditor ? [
                    'user_id' => (int) $row->last_edited_by,
                    'username' => $editorUsername,
                    'role' => $editorRole,
                ] : null,
                'updated_at' => $hasEditor ? $row->updated_at?->toDateTimeString() : null,
                'confidence' => $row->confidence,
                'status' => match (true) {
                    $row->pending => AnnotationStatusEnum::PENDING->value,
                    $row->annotations === null => AnnotationStatusEnum::NOT_ANNOTATED->value,
                    default => AnnotationStatusEnum::SUBMITTED->value,
                },
            ];

            $result[$dsId] = $entry;
        }

        return $result;
    }
}
