<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\SubProject;

final readonly class DeleteAnnotationsByProjectQuery {
    public function execute(int $projectId): void {
        Annotation::query()
            ->whereIn(
                'annotation_assignment_id',
                AnnotationAssignment::query()
                    ->whereIn(
                        'sub_project_id',
                        SubProject::query()->where('project_id', $projectId)->select('id'),
                    )
                    ->select('id'),
            )
            ->delete();
    }
}
