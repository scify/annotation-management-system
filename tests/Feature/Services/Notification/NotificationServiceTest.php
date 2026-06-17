<?php

declare(strict_types=1);

use App\Data\QuickLinkData;
use App\Enums\NotificationThreadResponseEnum;
use App\Enums\NotificationThreadTypeEnum;
use App\Enums\RolesEnum;
use App\Models\Notification;
use App\Models\NotificationThread;
use App\Models\NotificationThreadResponse;
use App\Models\QuickLink;
use App\Models\ThreadMember;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('NotificationService', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->service = resolve(NotificationService::class);
    });

    // --- createGenericNotification() & private createThreadMembers() ---

    it('creates a generic notification with recipient and sender members when a sender is given', function (): void {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();

        $notification = $this->service->createGenericNotification($recipient->id, 'Hello there', $sender->id);

        expect($notification->body)->toBe('Hello there')
            ->and($notification->sender_user_id)->toBe($sender->id);
        $this->assertDatabaseHas('notification_threads', [
            'id' => $notification->notification_thread_id,
            'type' => NotificationThreadTypeEnum::GENERIC->value,
        ]);
        $this->assertDatabaseHas('thread_members', ['notification_id' => $notification->id, 'user_id' => $recipient->id, 'is_read' => false]);
        $this->assertDatabaseHas('thread_members', ['notification_id' => $notification->id, 'user_id' => $sender->id, 'is_read' => true]);
        expect(ThreadMember::query()->where('notification_id', $notification->id)->count())->toBe(2);
    });

    it('creates a generic notification with a single member when no sender is given', function (): void {
        $recipient = User::factory()->create();

        $notification = $this->service->createGenericNotification($recipient->id, 'No sender');

        expect($notification->sender_user_id)->toBeNull();
        expect(ThreadMember::query()->where('notification_id', $notification->id)->count())->toBe(1);
    });

    // --- createWarningNotification() / createInfoNotification() ---

    it('creates one warning notification per recipient under a single titled thread', function (): void {
        $recipients = User::factory()->count(2)->create();

        $this->service->createWarningNotification($recipients->pluck('id')->all(), 'Heads up', 'Maintenance tonight');

        $this->assertDatabaseHas('notification_threads', [
            'type' => NotificationThreadTypeEnum::WARNING->value,
            'title' => 'Heads up',
        ]);
        expect(NotificationThread::query()->where('type', NotificationThreadTypeEnum::WARNING)->count())->toBe(1)
            ->and(Notification::query()->where('body', 'Maintenance tonight')->count())->toBe(2)
            ->and(ThreadMember::query()->count())->toBe(2);
    });

    it('creates one info notification per recipient under a single titled thread', function (): void {
        $recipients = User::factory()->count(2)->create();

        $this->service->createInfoNotification($recipients->pluck('id')->all(), 'FYI', 'New feature shipped');

        expect(NotificationThread::query()->where('type', NotificationThreadTypeEnum::INFO)->count())->toBe(1)
            ->and(Notification::query()->where('body', 'New feature shipped')->count())->toBe(2);
    });

    // --- createFlagNotification() / createInstanceRelatedNotification() ---

    it('creates a flag notification with two quick links', function (): void {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();

        $notification = $this->service->createFlagNotification(
            $recipient->id,
            $sender->id,
            'Flagged item',
            new QuickLinkData('First', '/first'),
            new QuickLinkData('Second', '/second'),
        );

        $this->assertDatabaseHas('notification_threads', [
            'id' => $notification->notification_thread_id,
            'type' => NotificationThreadTypeEnum::FLAG_NOTIFICATION->value,
        ]);
        expect(QuickLink::query()->where('notification_thread_id', $notification->notification_thread_id)->count())->toBe(2);
        $this->assertDatabaseHas('quick_links', ['label' => 'First', 'url' => '/first']);
        $this->assertDatabaseHas('quick_links', ['label' => 'Second', 'url' => '/second']);
    });

    it('creates an instance-related notification with two quick links', function (): void {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();

        $notification = $this->service->createInstanceRelatedNotification(
            $recipient->id,
            $sender->id,
            'Instance note',
            new QuickLinkData('Open', '/open'),
            new QuickLinkData('Resolve', '/resolve'),
        );

        $this->assertDatabaseHas('notification_threads', [
            'id' => $notification->notification_thread_id,
            'type' => NotificationThreadTypeEnum::INSTANCE_RELATED->value,
        ]);
        expect(QuickLink::query()->where('notification_thread_id', $notification->notification_thread_id)->count())->toBe(2);
    });

    // --- createAnnouncementNotification() ---

    it('creates one announcement notification per recipient with a single shared quick link', function (): void {
        $recipients = User::factory()->count(2)->create();
        $sender = User::factory()->create();

        $this->service->createAnnouncementNotification(
            $recipients->pluck('id')->all(),
            $sender->id,
            'Big news',
            new QuickLinkData('Read more', '/news'),
        );

        $thread = NotificationThread::query()->where('type', NotificationThreadTypeEnum::ANNOUNCEMENT)->firstOrFail();
        expect(Notification::query()->where('notification_thread_id', $thread->id)->count())->toBe(2)
            ->and(QuickLink::query()->where('notification_thread_id', $thread->id)->count())->toBe(1);
    });

    // --- createProjectOwnershipNotification() / createProjectInvitationNotification() ---

    it('creates a project ownership notification with a thread response and quick link', function (): void {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();

        $notification = $this->service->createProjectOwnershipNotification(
            $recipient->id,
            $sender->id,
            'Ownership offer',
            new QuickLinkData('Decide', '/decide'),
        );

        $this->assertDatabaseHas('notification_threads', [
            'id' => $notification->notification_thread_id,
            'type' => NotificationThreadTypeEnum::PROJECT_OWNERSHIP->value,
        ]);
        $this->assertDatabaseHas('notification_thread_responses', [
            'notification_thread_id' => $notification->notification_thread_id,
            'response' => NotificationThreadResponseEnum::UNREPLIED->value,
        ]);
        expect(QuickLink::query()->where('notification_thread_id', $notification->notification_thread_id)->count())->toBe(1);
    });

    it('creates a project invitation notification with a thread response and quick link', function (): void {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();

        $notification = $this->service->createProjectInvitationNotification(
            $recipient->id,
            $sender->id,
            'Join the project',
            new QuickLinkData('Accept', '/accept'),
        );

        $this->assertDatabaseHas('notification_threads', [
            'id' => $notification->notification_thread_id,
            'type' => NotificationThreadTypeEnum::PROJECT_INVITATION->value,
        ]);
        $this->assertDatabaseHas('notification_thread_responses', [
            'notification_thread_id' => $notification->notification_thread_id,
        ]);
    });

    // --- replyToGenericNotification() ---

    it('replies to a generic thread without creating a new thread', function (): void {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();
        $original = $this->service->createGenericNotification($recipient->id, 'First message', $sender->id);
        $threadCountBefore = NotificationThread::query()->count();

        $reply = $this->service->replyToGenericNotification(
            $original->notification_thread_id,
            $recipient->id,
            'A reply',
            $sender->id,
        );

        expect($reply->notification_thread_id)->toBe($original->notification_thread_id)
            ->and(NotificationThread::query()->count())->toBe($threadCountBefore)
            ->and(Notification::query()->where('notification_thread_id', $original->notification_thread_id)->count())->toBe(2);
    });

    // --- mark / unread delegators ---

    it('marks a thread membership as read and then unread', function (): void {
        $user = User::factory()->create();
        $thread = NotificationThread::factory()->create();
        $notification = Notification::factory()->create(['notification_thread_id' => $thread->id]);
        ThreadMember::factory()->create(['notification_id' => $notification->id, 'user_id' => $user->id, 'is_read' => false]);

        $this->service->markAsRead($thread->id, $user->id);
        $this->assertDatabaseHas('thread_members', ['user_id' => $user->id, 'is_read' => true]);

        $this->service->markAsUnread($thread->id, $user->id);
        $this->assertDatabaseHas('thread_members', ['user_id' => $user->id, 'is_read' => false]);
    });

    it("marks all of a user's memberships as read", function (): void {
        $user = User::factory()->create();
        foreach (range(1, 2) as $i) {
            $thread = NotificationThread::factory()->create();
            $notification = Notification::factory()->create(['notification_thread_id' => $thread->id]);
            ThreadMember::factory()->create(['notification_id' => $notification->id, 'user_id' => $user->id, 'is_read' => false]);
        }

        $this->service->markAllAsRead($user->id);

        $this->assertDatabaseMissing('thread_members', ['user_id' => $user->id, 'is_read' => false]);
    });

    // --- hasUnreadNotifications() ---

    it('reports whether a user has any unread notifications', function (): void {
        $user = User::factory()->create();
        $thread = NotificationThread::factory()->create();
        $notification = Notification::factory()->create(['notification_thread_id' => $thread->id]);
        $member = ThreadMember::factory()->create(['notification_id' => $notification->id, 'user_id' => $user->id, 'is_read' => false]);

        expect($this->service->hasUnreadNotifications($user->id))->toBeTrue();

        $member->update(['is_read' => true]);
        expect($this->service->hasUnreadNotifications($user->id))->toBeFalse();
    });

    it('reports no unread notifications for a user with none', function (): void {
        $user = User::factory()->create();

        expect($this->service->hasUnreadNotifications($user->id))->toBeFalse();
    });

    // --- getMyNotifications() transformation ---

    it('returns an empty collection for a user with no notifications', function (): void {
        $user = User::factory()->create();

        expect($this->service->getMyNotifications($user->id))->toHaveCount(0);
    });

    it('exposes a generic thread as repliable and titles it by the sender', function (): void {
        $user = User::factory()->create();
        $sender = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);
        $notification = Notification::factory()->create([
            'notification_thread_id' => $thread->id,
            'sender_user_id' => $sender->id,
        ]);
        ThreadMember::factory()->create(['notification_id' => $notification->id, 'user_id' => $user->id, 'is_read' => false]);

        $result = $this->service->getMyNotifications($user->id);

        $found = $result->firstWhere('id', $thread->id);
        expect($found->allowed_to_reply)->toBeTrue()
            ->and($found->top_right)->toBeNull()
            ->and($found->replied_by)->toBeNull()
            ->and($found->title)->toBe($sender->username)
            ->and($found->is_read)->toBeFalse();
    });

    it('falls back to the other member username for a generic thread without a distinct sender', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);
        $notification = Notification::factory()->create([
            'notification_thread_id' => $thread->id,
            'sender_user_id' => null,
        ]);
        ThreadMember::factory()->create(['notification_id' => $notification->id, 'user_id' => $user->id]);
        ThreadMember::factory()->create(['notification_id' => $notification->id, 'user_id' => $other->id]);

        $found = $this->service->getMyNotifications($user->id)->firstWhere('id', $thread->id);

        expect($found->title)->toBe($other->username);
    });

    it('shows the first quick link as top_right for a flag thread and allows replies', function (): void {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::FLAG_NOTIFICATION]);
        $notification = Notification::factory()->create(['notification_thread_id' => $thread->id, 'sender_user_id' => $sender->id]);
        ThreadMember::factory()->create(['notification_id' => $notification->id, 'user_id' => $user->id]);
        QuickLink::factory()->create(['notification_thread_id' => $thread->id, 'label' => 'Go to instance']);

        $found = $this->service->getMyNotifications($user->id)->firstWhere('id', $thread->id);

        expect($found->allowed_to_reply)->toBeTrue()
            ->and($found->top_right)->toBe('Go to instance');
    });

    it('shows the quick link as top_right for an announcement but does not allow replies', function (): void {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::ANNOUNCEMENT]);
        $notification = Notification::factory()->create(['notification_thread_id' => $thread->id, 'sender_user_id' => $sender->id]);
        ThreadMember::factory()->create(['notification_id' => $notification->id, 'user_id' => $user->id]);
        QuickLink::factory()->create(['notification_thread_id' => $thread->id, 'label' => 'Announcement link']);

        $found = $this->service->getMyNotifications($user->id)->firstWhere('id', $thread->id);

        expect($found->allowed_to_reply)->toBeFalse()
            ->and($found->top_right)->toBe('Announcement link')
            ->and($found->title)->toBe($sender->username);
    });

    it('exposes the response value and Ownership top_right for a project ownership thread', function (): void {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::PROJECT_OWNERSHIP]);
        $notification = Notification::factory()->create(['notification_thread_id' => $thread->id, 'sender_user_id' => $sender->id]);
        ThreadMember::factory()->create(['notification_id' => $notification->id, 'user_id' => $user->id]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::UNREPLIED,
        ]);

        $found = $this->service->getMyNotifications($user->id)->firstWhere('id', $thread->id);

        expect($found->top_right)->toBe('Ownership')
            ->and($found->response)->toBe(NotificationThreadResponseEnum::UNREPLIED->value);
    });

    it('shows the Invitation to Project top_right for a project invitation thread', function (): void {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::PROJECT_INVITATION]);
        $notification = Notification::factory()->create(['notification_thread_id' => $thread->id, 'sender_user_id' => $sender->id]);
        ThreadMember::factory()->create(['notification_id' => $notification->id, 'user_id' => $user->id]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::ACCEPTED,
        ]);

        $found = $this->service->getMyNotifications($user->id)->firstWhere('id', $thread->id);

        expect($found->top_right)->toBe('Invitation to Project')
            ->and($found->response)->toBe(NotificationThreadResponseEnum::ACCEPTED->value);
    });

    it('does not set a title and disallows replies for warning threads', function (): void {
        $user = User::factory()->create();
        $thread = NotificationThread::factory()->create([
            'type' => NotificationThreadTypeEnum::WARNING,
            'title' => 'Original warning title',
        ]);
        $notification = Notification::factory()->create(['notification_thread_id' => $thread->id]);
        ThreadMember::factory()->create(['notification_id' => $notification->id, 'user_id' => $user->id]);

        $found = $this->service->getMyNotifications($user->id)->firstWhere('id', $thread->id);

        expect($found->allowed_to_reply)->toBeFalse()
            ->and($found->top_right)->toBeNull()
            ->and($found->title)->toBe('Original warning title');
    });

    it('marks a thread as read only when every membership is read', function (): void {
        $user = User::factory()->create();
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);
        $notification = Notification::factory()->create(['notification_thread_id' => $thread->id]);
        ThreadMember::factory()->create(['notification_id' => $notification->id, 'user_id' => $user->id, 'is_read' => true]);

        $found = $this->service->getMyNotifications($user->id)->firstWhere('id', $thread->id);

        expect($found->is_read)->toBeTrue();
    });

    it('reports replied_by and transformed notification attributes for multi-notification threads', function (): void {
        $user = User::factory()->create();
        $sender = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);

        $first = Notification::factory()->create([
            'notification_thread_id' => $thread->id,
            'sender_user_id' => $user->id,
            'created_at' => now()->subMinutes(5),
        ]);
        ThreadMember::factory()->create(['notification_id' => $first->id, 'user_id' => $user->id]);

        $latest = Notification::factory()->create([
            'notification_thread_id' => $thread->id,
            'sender_user_id' => $sender->id,
            'created_at' => now(),
        ]);
        ThreadMember::factory()->create(['notification_id' => $latest->id, 'user_id' => $user->id]);

        $found = $this->service->getMyNotifications($user->id)->firstWhere('id', $thread->id);

        expect($found->replied_by)->toBe($sender->username);
        $transformed = $found->notifications->firstWhere('id', $latest->id);
        expect($transformed->sender_username)->toBe($sender->username)
            ->and($transformed->sender_role)->toBe(RolesEnum::ANNOTATOR->value)
            ->and($transformed->datetime)->not->toBeNull();
    });

    it('returns threads sorted by most recent activity first', function (): void {
        $user = User::factory()->create();

        $older = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);
        $olderNotification = Notification::factory()->create(['notification_thread_id' => $older->id, 'created_at' => now()->subDays(2)]);
        ThreadMember::factory()->create(['notification_id' => $olderNotification->id, 'user_id' => $user->id]);

        $newer = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);
        $newerNotification = Notification::factory()->create(['notification_thread_id' => $newer->id, 'created_at' => now()]);
        ThreadMember::factory()->create(['notification_id' => $newerNotification->id, 'user_id' => $user->id]);

        $result = $this->service->getMyNotifications($user->id);

        expect($result->first()->id)->toBe($newer->id)
            ->and($result->last()->id)->toBe($older->id);
    });
});
