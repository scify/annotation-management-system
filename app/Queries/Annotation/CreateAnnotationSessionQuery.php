<?php

declare(strict_types=1);

namespace App\Queries\Annotation;

use App\Models\AnnotationSession;

final readonly class CreateAnnotationSessionQuery {
    public function create(int $annotationAssignmentId, int $nextAnnotationId): int {
        $session = AnnotationSession::query()->create([
            'annotation_assignment_id' => $annotationAssignmentId,
            'next_annotation_id' => $nextAnnotationId,
            'started_timestamp' => now(),
            'session_annotations_count' => 0,
        ]);

        return $session->id;
    }
}
