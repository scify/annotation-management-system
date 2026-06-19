<?php

declare(strict_types=1);

use App\Enums\NotificationThreadResponseEnum;
use App\Enums\NotificationThreadTypeEnum;
use App\Exceptions\NotificationResponseException;
use App\Models\NotificationThread;
use App\Models\NotificationThreadResponse;
use App\Services\Notification\ProjectInvitationNotificationService;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * Builds a PROJECT_INVITATION thread, optionally with a response row in the given state.
 * Invitation has no CANCELED branch: approve only guards against REJECTED, reject only
 * against ACCEPTED. With no project context the ProjectManagerService call is skipped.
 */
function makeInvitationThread(?NotificationThreadResponseEnum $response): NotificationThread {
    $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::PROJECT_INVITATION]);

    if ($response instanceof NotificationThreadResponseEnum) {
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'response' => $response,
        ]);
    }

    return $thread;
}

describe('ProjectInvitationNotificationService', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->service = resolve(ProjectInvitationNotificationService::class);
    });

    // --- approve() ---

    it('throws when there is no response record to approve', function (): void {
        $thread = makeInvitationThread(null);

        expect(function () use ($thread): void {
            $this->service->approve($thread->id);
        })->toThrow(NotificationResponseException::class, __('notifications.errors.response_not_found'));
    });

    it('throws when approving an already-rejected thread', function (): void {
        $thread = makeInvitationThread(NotificationThreadResponseEnum::REJECTED);

        expect(function () use ($thread): void {
            $this->service->approve($thread->id);
        })->toThrow(NotificationResponseException::class, __('notifications.errors.cannot_approve_rejected'));
    });

    it('transitions an unreplied thread to accepted', function (): void {
        $thread = makeInvitationThread(NotificationThreadResponseEnum::UNREPLIED);

        $this->service->approve($thread->id);

        $this->assertDatabaseHas('notification_thread_responses', [
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::ACCEPTED->value,
        ]);
    });

    // --- reject() ---

    it('throws when there is no response record to reject', function (): void {
        $thread = makeInvitationThread(null);

        expect(function () use ($thread): void {
            $this->service->reject($thread->id);
        })->toThrow(NotificationResponseException::class, __('notifications.errors.response_not_found'));
    });

    it('throws when rejecting an already-accepted thread', function (): void {
        $thread = makeInvitationThread(NotificationThreadResponseEnum::ACCEPTED);

        expect(function () use ($thread): void {
            $this->service->reject($thread->id);
        })->toThrow(NotificationResponseException::class, __('notifications.errors.cannot_reject_accepted'));
    });

    it('transitions an unreplied thread to rejected', function (): void {
        $thread = makeInvitationThread(NotificationThreadResponseEnum::UNREPLIED);

        $this->service->reject($thread->id);

        $this->assertDatabaseHas('notification_thread_responses', [
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::REJECTED->value,
        ]);
    });
});
