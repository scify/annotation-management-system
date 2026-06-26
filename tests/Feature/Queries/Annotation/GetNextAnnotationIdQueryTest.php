<?php

declare(strict_types=1);

use App\Enums\NotificationThreadTypeEnum;
use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\Notification;
use App\Models\NotificationThread;
use App\Queries\Annotation\GetNextAnnotationIdQuery;

describe('GetNextAnnotationIdQuery', function (): void {
    it('returns annotations in priority order: not-annotated → pending → flagged-replied → flagged-not-replied', function (): void {
        // Arrange
        $query = new GetNextAnnotationIdQuery();
        $assignment = AnnotationAssignment::factory()->create();

        $makeAnnotation = fn (int $index, array $overrides = []) => Annotation::factory()->create(array_merge([
            'annotation_assignment_id' => $assignment->id,
            'annotator_instance_index' => $index,
            'project_instance_index' => $index,
        ], $overrides));

        $flagThread = fn (int $notificationCount) => NotificationThread::factory()
            ->has(Notification::factory()->count($notificationCount), 'notifications')
            ->create(['type' => NotificationThreadTypeEnum::FLAG_NOTIFICATION]);

        $markDone = fn (int $id) => Annotation::query()->where('id', $id)->update([
            'annotations' => '[]',
            'pending' => false,
            'flag_notification_thread_id' => null,
        ]);

        // 4 not-annotated (annotations=null, no flag thread)
        $notAnnotated = collect(range(1, 4))->map(
            fn (int $i) => $makeAnnotation($i),
        );

        // 3 pending (has annotations content, pending=true)
        $pending = collect(range(5, 7))->map(
            fn (int $i) => $makeAnnotation($i, ['annotations' => [], 'pending' => true]),
        );

        // 2 flagged + replied (flag thread with >= 2 notifications)
        $flaggedReplied = collect(range(8, 9))->map(
            fn (int $i) => $makeAnnotation($i, ['flag_notification_thread_id' => $flagThread(2)->id]),
        );

        // 2 flagged + not replied (flag thread with < 2 notifications)
        $flaggedNotReplied = collect(range(10, 11))->map(
            fn (int $i) => $makeAnnotation($i, ['flag_notification_thread_id' => $flagThread(1)->id]),
        );

        // 1 fully submitted — must never be returned
        $makeAnnotation(12, ['annotations' => [], 'pending' => false]);

        // Act & Assert — not-annotated phase
        foreach ($notAnnotated as $annotation) {
            expect($query->get($assignment->id))->toBe($annotation->id);
            $markDone($annotation->id);
        }

        // Act & Assert — pending phase
        foreach ($pending as $annotation) {
            expect($query->get($assignment->id))->toBe($annotation->id);
            $markDone($annotation->id);
        }

        // Act & Assert — flagged + replied phase
        foreach ($flaggedReplied as $annotation) {
            expect($query->get($assignment->id))->toBe($annotation->id);
            $markDone($annotation->id);
        }

        // Act & Assert — flagged + not replied phase
        foreach ($flaggedNotReplied as $annotation) {
            expect($query->get($assignment->id))->toBe($annotation->id);
            $markDone($annotation->id);
        }

        // All annotations resolved — nothing left to return
        expect($query->get($assignment->id))->toBeNull();
    });
});
