<?php

declare(strict_types=1);

namespace App\Queries\Annotation;

use App\Models\AnnotationSession;

final readonly class StopAnnotationSessionQuery {
    public function stop(int $annotationSessionId): void {
        AnnotationSession::query()
            ->where('id', $annotationSessionId)
            ->update(['ended_timestamp' => now()]);
    }
}
