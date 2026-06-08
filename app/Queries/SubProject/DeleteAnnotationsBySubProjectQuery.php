<?php

declare(strict_types=1);

namespace App\Queries\SubProject;

use App\Models\Annotation;
use App\Models\AnnotationAssignment;

final readonly class DeleteAnnotationsBySubProjectQuery {
    public function execute(int $subProjectId): void {
        Annotation::query()
            ->whereIn(
                'annotation_assignment_id',
                AnnotationAssignment::query()->where('sub_project_id', $subProjectId)->select('id'),
            )
            ->delete();
    }
}
