<?php

declare(strict_types=1);

namespace App\Queries\Annotation;

use App\Models\AnnotationSession;

final readonly class GetAnnotationSessionByIdQuery {
    public function get(int $id): ?AnnotationSession {
        return AnnotationSession::query()->find($id);
    }
}
