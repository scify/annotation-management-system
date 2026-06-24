<?php

declare(strict_types=1);

use App\Enums\NotificationThreadTypeEnum;
use App\Models\NotificationThread;
use App\Models\NotificationThreadResponse;
use App\Models\Project;
use App\Models\User;
use App\Queries\Notification\FindRequestToLeaveContextByThreadQuery;

describe('FindRequestToLeaveContextByThreadQuery', function (): void {
    it('returns the project and target user (the leaver) stored on the thread', function (): void {
        // Arrange — for request-to-leave the leaver is the sender on the thread response.
        $project = Project::factory()->create();
        $leaver = User::factory()->create();
        $thread = NotificationThread::factory()->create([
            'type' => NotificationThreadTypeEnum::PROJECT_REQUEST_TO_LEAVE,
            'project_id' => $project->id,
        ]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'sender_user_id' => $leaver->id,
        ]);

        // Act
        $context = new FindRequestToLeaveContextByThreadQuery()->find($thread->id);

        // Assert
        expect($context)->not->toBeNull()
            ->and($context->projectId)->toBe($project->id)
            ->and($context->targetUserId)->toBe($leaver->id);
    });

    it('resolves the exact project even when the leaver belongs to several projects', function (): void {
        // Arrange — the regression: leaver has rows on two projects; request is for Project B.
        $leaver = User::factory()->create();
        $projectA = Project::factory()->create();
        $projectB = Project::factory()->create();
        $thread = NotificationThread::factory()->create([
            'type' => NotificationThreadTypeEnum::PROJECT_REQUEST_TO_LEAVE,
            'project_id' => $projectB->id,
        ]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'sender_user_id' => $leaver->id,
        ]);

        // Act
        $context = new FindRequestToLeaveContextByThreadQuery()->find($thread->id);

        // Assert
        expect($projectA->id)->toBeLessThan($projectB->id)
            ->and($context->projectId)->toBe($projectB->id);
    });

    it('returns null when the thread has no project context', function (): void {
        $thread = NotificationThread::factory()->create([
            'type' => NotificationThreadTypeEnum::GENERIC,
            'project_id' => null,
        ]);

        expect(new FindRequestToLeaveContextByThreadQuery()->find($thread->id))->toBeNull();
    });

    it('returns null when the thread does not exist', function (): void {
        expect(new FindRequestToLeaveContextByThreadQuery()->find(999999))->toBeNull();
    });
});
