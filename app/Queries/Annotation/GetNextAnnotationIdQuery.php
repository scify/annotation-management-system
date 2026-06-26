<?php

declare(strict_types=1);

namespace App\Queries\Annotation;

use App\Models\Annotation;

final readonly class GetNextAnnotationIdQuery {
    /**
     * Returns the id of the next annotation to work on for a given assignment.
     *
     * Priority:
     *   1. First not-annotated, not-flagged row (annotations IS NULL, flag_notification_thread_id IS NULL)
     *   2. First pending row (pending = true)
     *   3. First flagged + replied row (flag thread has >= 2 notifications)
     *   4. First flagged + not-replied row (flag thread has < 2 notifications)
     */
    public function get(int $annotationAssignmentId): ?int {
        $base = Annotation::query()
            ->where('annotation_assignment_id', $annotationAssignmentId)
            ->orderBy('annotator_instance_index');

        // 1. Not annotated, not flagged
        $row = $base->clone()
            ->whereNull('annotations')
            ->whereNull('flag_notification_thread_id')
            ->first(['id']);

        if ($row !== null) {
            return $row->id;
        }

        // 2. Pending
        $row = $base->clone()
            ->where('pending', true)
            ->first(['id']);

        if ($row !== null) {
            return $row->id;
        }

        $flaggedBase = $base->clone()
            ->whereNull('annotations')
            ->whereNotNull('flag_notification_thread_id');

        // 3. Flagged + replied (thread has more than 1 notification)
        $row = $flaggedBase->clone()
            ->has('flagNotificationThread.notifications', '>=', 2)
            ->first(['id']);

        if ($row !== null) {
            return $row->id;
        }

        // 4. Flagged + not replied (thread has exactly 1 notification — the flag itself)
        return $flaggedBase->clone()
            ->has('flagNotificationThread.notifications', '=', 1)
            ->first(['id'])?->id;
    }
}
