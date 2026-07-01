<?php

declare(strict_types=1);

namespace App\Queries\Annotation;

use App\Models\AnnotationSession;

final readonly class IncrementAnnotationSessionCountQuery {
    public function increment(int $annotationSessionId): void {
        AnnotationSession::query()
            ->where('id', $annotationSessionId)
            ->increment('session_annotations_count');
    }
}
