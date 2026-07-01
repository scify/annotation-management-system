<?php

declare(strict_types=1);

namespace App\Queries\Annotation;

use App\Models\AnnotationSession;

final readonly class UpdateAnnotationSessionNextAnnotationQuery {
    public function update(int $annotationSessionId, int $nextAnnotationId): void {
        AnnotationSession::query()
            ->where('id', $annotationSessionId)
            ->update(['next_annotation_id' => $nextAnnotationId]);
    }
}
