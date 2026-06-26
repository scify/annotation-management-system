<?php

declare(strict_types=1);

namespace App\Queries\Annotation;

use App\Models\Annotation;

final readonly class GetFlaggedAnnotationCountsQuery {
    /**
     * Returns flagged and replied-flagged annotation counts for a user's assignment in a sub-project.
     *
     * Flagged: flag_notification_thread_id IS NOT NULL AND annotations IS NULL.
     * Replied: flagged AND the thread has >= 2 notifications.
     *
     * @return array{flagged_count: int, replied_flagged_count: int}
     */
    public function get(int $subProjectId, int $userId): array {
        $base = Annotation::query()
            ->join('annotation_assignments', 'annotation_assignments.id', '=', 'annotations.annotation_assignment_id')
            ->where('annotation_assignments.sub_project_id', $subProjectId)
            ->where('annotation_assignments.user_id', $userId)
            ->whereNotNull('annotations.flag_notification_thread_id')
            ->whereNull('annotations.annotations');

        return [
            'flagged_count' => $base->clone()->count(),
            'replied_flagged_count' => $base->clone()->has('flagNotificationThread.notifications', '>=', 2)->count(),
        ];
    }
}
