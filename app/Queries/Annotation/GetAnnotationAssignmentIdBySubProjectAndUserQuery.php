<?php

declare(strict_types=1);

namespace App\Queries\Annotation;

use App\Models\AnnotationAssignment;

final readonly class GetAnnotationAssignmentIdBySubProjectAndUserQuery {
    public function get(int $subProjectId, int $userId): ?int {
        /** @var AnnotationAssignment|null $assignment */
        $assignment = AnnotationAssignment::query()
            ->select('id')
            ->where('sub_project_id', $subProjectId)
            ->where('user_id', $userId)
            ->first();

        return $assignment?->id;
    }
}
