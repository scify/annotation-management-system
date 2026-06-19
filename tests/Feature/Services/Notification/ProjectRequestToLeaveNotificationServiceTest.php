<?php

declare(strict_types=1);

use App\Data\QuickLinkData;
use App\Enums\NotificationThreadResponseEnum;
use App\Enums\NotificationThreadTypeEnum;
use App\Exceptions\NotificationResponseException;
use App\Models\NotificationThread;
use App\Models\NotificationThreadResponse;
use App\Models\QuickLink;
use App\Models\ThreadMember;
use App\Models\User;
use App\Services\Notification\ProjectRequestToLeaveNotificationService;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * Builds a PROJECT_REQUEST_TO_LEAVE thread, optionally with a response row in the given
 * state. With no matching project the member-context lookup resolves to null, so
 * approve/reject simply transition the response and skip ProjectManagerService.
 */
function makeRequestToLeaveThread(?NotificationThreadResponseEnum $response): NotificationThread {
    $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::PROJECT_REQUEST_TO_LEAVE]);

    if ($response instanceof NotificationThreadResponseEnum) {
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'response' => $response,
        ]);
    }

    return $thread;
}

describe('ProjectRequestToLeaveNotificationService', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->service = resolve(ProjectRequestToLeaveNotificationService::class);
    });

    // --- createNotification() ---

    it('creates a request-to-leave thread with both members, a response and a quick link', function (): void {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $notification = $this->service->createNotification(
            recipientUserId: $recipient->id,
            senderUserId: $sender->id,
            body: 'Please let me leave',
            quickLink: new QuickLinkData('Open project', '/projects/1'),
        );

        $this->assertDatabaseHas('notification_threads', [
            'id' => $notification->notification_thread_id,
            'type' => NotificationThreadTypeEnum::PROJECT_REQUEST_TO_LEAVE->value,
        ]);
        $this->assertDatabaseHas('thread_members', ['notification_thread_id' => $notification->notification_thread_id, 'user_id' => $sender->id, 'is_read' => true]);
        $this->assertDatabaseHas('thread_members', ['notification_thread_id' => $notification->notification_thread_id, 'user_id' => $recipient->id, 'is_read' => false]);
        $this->assertDatabaseHas('notification_thread_responses', [
            'notification_thread_id' => $notification->notification_thread_id,
            'response' => NotificationThreadResponseEnum::UNREPLIED->value,
        ]);
        expect(QuickLink::query()->where('notification_thread_id', $notification->notification_thread_id)->count())->toBe(1)
            ->and(ThreadMember::query()->where('notification_thread_id', $notification->notification_thread_id)->count())->toBe(2);
    });

    // --- approve() ---

    it('throws when there is no response record to approve', function (): void {
        $thread = makeRequestToLeaveThread(null);

        expect(function () use ($thread): void {
            $this->service->approve($thread->id);
        })->toThrow(NotificationResponseException::class, __('notifications.errors.response_not_found'));
    });

    it('throws when approving a cancelled thread', function (): void {
        $thread = makeRequestToLeaveThread(NotificationThreadResponseEnum::CANCELED);

        expect(function () use ($thread): void {
            $this->service->approve($thread->id);
        })->toThrow(NotificationResponseException::class, __('notifications.errors.cannot_respond_cancelled'));
    });

    it('throws when approving an already-rejected thread', function (): void {
        $thread = makeRequestToLeaveThread(NotificationThreadResponseEnum::REJECTED);

        expect(function () use ($thread): void {
            $this->service->approve($thread->id);
        })->toThrow(NotificationResponseException::class, __('notifications.errors.cannot_approve_rejected'));
    });

    it('transitions an unreplied thread to accepted', function (): void {
        $thread = makeRequestToLeaveThread(NotificationThreadResponseEnum::UNREPLIED);

        $this->service->approve($thread->id);

        $this->assertDatabaseHas('notification_thread_responses', [
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::ACCEPTED->value,
        ]);
    });

    // --- reject() ---

    it('throws when there is no response record to reject', function (): void {
        $thread = makeRequestToLeaveThread(null);

        expect(function () use ($thread): void {
            $this->service->reject($thread->id);
        })->toThrow(NotificationResponseException::class, __('notifications.errors.response_not_found'));
    });

    it('throws when rejecting a cancelled thread', function (): void {
        $thread = makeRequestToLeaveThread(NotificationThreadResponseEnum::CANCELED);

        expect(function () use ($thread): void {
            $this->service->reject($thread->id);
        })->toThrow(NotificationResponseException::class, __('notifications.errors.cannot_respond_cancelled'));
    });

    it('throws when rejecting an already-accepted thread', function (): void {
        $thread = makeRequestToLeaveThread(NotificationThreadResponseEnum::ACCEPTED);

        expect(function () use ($thread): void {
            $this->service->reject($thread->id);
        })->toThrow(NotificationResponseException::class, __('notifications.errors.cannot_reject_accepted'));
    });

    it('transitions an unreplied thread to rejected', function (): void {
        $thread = makeRequestToLeaveThread(NotificationThreadResponseEnum::UNREPLIED);

        $this->service->reject($thread->id);

        $this->assertDatabaseHas('notification_thread_responses', [
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::REJECTED->value,
        ]);
    });
});
