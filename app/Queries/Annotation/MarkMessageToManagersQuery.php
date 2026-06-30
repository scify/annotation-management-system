<?php

declare(strict_types=1);

namespace App\Queries\Annotation;

use App\Models\Annotation;

final readonly class MarkMessageToManagersQuery {
    public function mark(int $annotationAssignmentId, int $annotatorInstanceIndex, int $notificationThreadId): void {
        Annotation::query()
            ->where('annotation_assignment_id', $annotationAssignmentId)
            ->where('annotator_instance_index', $annotatorInstanceIndex)
            ->update(['message_to_managers_notification_thread_id' => $notificationThreadId]);
    }
}
