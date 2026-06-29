<?php

declare(strict_types=1);

namespace App\Queries\Annotation;

use App\Enums\ConfidenceEnum;
use App\Models\Annotation;

final readonly class UpdateAnnotationQuery {
    /** @param array<string, mixed> $annotations */
    public function update(int $annotationId, array $annotations, bool $pending, ?ConfidenceEnum $confidence): void {
        Annotation::query()->where('id', $annotationId)->update([
            'annotations' => $annotations,
            'pending' => $pending,
            'confidence' => $confidence,
        ]);
    }
}
