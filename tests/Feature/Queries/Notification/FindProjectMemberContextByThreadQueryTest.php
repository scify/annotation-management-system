<?php

declare(strict_types=1);

use App\Enums\NotificationThreadTypeEnum;
use App\Models\NotificationThread;
use App\Models\NotificationThreadResponse;
use App\Models\Project;
use App\Models\User;
use App\Queries\Notification\FindProjectMemberContextByThreadQuery;

describe('FindProjectMemberContextByThreadQuery', function (): void {
    it('returns the project and target user stored on the thread', function (): void {
        // Arrange — target user is the recipient on the thread response.
        $project = Project::factory()->create();
        $target = User::factory()->create();
        $thread = NotificationThread::factory()->create([
            'type' => NotificationThreadTypeEnum::PROJECT_INVITATION,
            'project_id' => $project->id,
        ]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'recipient_user_id' => $target->id,
        ]);

        // Act
        $context = new FindProjectMemberContextByThreadQuery()->find($thread->id);

        // Assert
        expect($context)->not->toBeNull()
            ->and($context->projectId)->toBe($project->id)
            ->and($context->targetUserId)->toBe($target->id);
    });

    it('resolves the exact project on the thread even when the sender owns several projects', function (): void {
        // Arrange — the regression: sender owns Project A (lower id), invitation is for Project B.
        $sender = User::factory()->create();
        $projectA = Project::factory()->create(['owner_user_id' => $sender->id]);
        $projectB = Project::factory()->create(['owner_user_id' => $sender->id]);
        $target = User::factory()->create();
        $thread = NotificationThread::factory()->create([
            'type' => NotificationThreadTypeEnum::PROJECT_INVITATION,
            'project_id' => $projectB->id,
        ]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'recipient_user_id' => $target->id,
        ]);

        // Act
        $context = new FindProjectMemberContextByThreadQuery()->find($thread->id);

        // Assert — must be B, not the sender's first-owned project A.
        expect($projectA->id)->toBeLessThan($projectB->id)
            ->and($context->projectId)->toBe($projectB->id);
    });

    it('returns null when the thread has no project context', function (): void {
        $thread = NotificationThread::factory()->create([
            'type' => NotificationThreadTypeEnum::GENERIC,
            'project_id' => null,
        ]);

        expect(new FindProjectMemberContextByThreadQuery()->find($thread->id))->toBeNull();
    });

    it('returns null when only the target user is set but the project is missing', function (): void {
        // Arrange — response has a recipient but thread has no project.
        $target = User::factory()->create();
        $thread = NotificationThread::factory()->create([
            'type' => NotificationThreadTypeEnum::PROJECT_INVITATION,
            'project_id' => null,
        ]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'recipient_user_id' => $target->id,
        ]);

        expect(new FindProjectMemberContextByThreadQuery()->find($thread->id))->toBeNull();
    });

    it('returns null when the thread does not exist', function (): void {
        expect(new FindProjectMemberContextByThreadQuery()->find(999999))->toBeNull();
    });
});
