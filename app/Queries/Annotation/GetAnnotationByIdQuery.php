<?php

declare(strict_types=1);

namespace App\Queries\Annotation;

use App\Models\Annotation;

final readonly class GetAnnotationByIdQuery {
    public function get(int $id): ?Annotation {
        return Annotation::query()->select(['id', 'annotator_instance_index'])->find($id);
    }
}
