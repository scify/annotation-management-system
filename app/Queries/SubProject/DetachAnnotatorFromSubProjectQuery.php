<?php

declare(strict_types=1);

namespace App\Queries\SubProject;

use App\Models\Annotation;
use App\Models\AnnotationAssignment;

final readonly class DetachAnnotatorFromSubProjectQuery {
    public function detach(int $subProjectId, int $annotatorId): void {
        $assignment = AnnotationAssignment::query()
            ->where('sub_project_id', $subProjectId)
            ->where('user_id', $annotatorId)
            ->firstOrFail();

        Annotation::query()
            ->where('annotation_assignment_id', $assignment->id)
            ->delete();

        $assignment->delete();
    }
}
