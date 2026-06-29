<?php

declare(strict_types=1);

namespace App\Queries\Annotation;

use App\Models\Annotation;

final readonly class SubmitPendingAnnotationsQuery {
    public function submit(int $annotationAssignmentId, int $userId): void {
        Annotation::query()
            ->where('annotation_assignment_id', $annotationAssignmentId)
            ->where('pending', true)
            ->update([
                'pending' => false,
                'last_edited_by' => $userId,
            ]);
    }
}
