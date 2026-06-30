<?php

declare(strict_types=1);

use App\Data\QuickLinkData;
use App\Enums\NotificationThreadResponseEnum;
use App\Enums\NotificationThreadTypeEnum;
use App\Enums\RolesEnum;
use App\Models\Notification;
use App\Models\NotificationThread;
use App\Models\NotificationThreadResponse;
use App\Models\Project;
use App\Models\QuickLink;
use App\Models\ThreadMember;
use App\Models\User;
use App\Services\Notification\AnnouncementNotificationService;
use App\Services\Notification\FlagNotificationService;
use App\Services\Notification\GenericNotificationService;
use App\Services\Notification\InfoNotificationService;
use App\Services\Notification\InstanceRelatedNotificationService;
use App\Services\Notification\NotificationsService;
use App\Services\Notification\ProjectInvitationNotificationService;
use App\Services\Notification\ProjectOwnershipNotificationService;
use App\Services\Notification\WarningNotificationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

describe('NotificationsService', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->service = resolve(NotificationsService::class);
    });

    // --- GenericNotificationService ---

    it('creates a generic notification with recipient and sender as thread members', function (): void {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();

        $notification = resolve(GenericNotificationService::class)->createNotification($recipient->id, 'Hello there', $sender->id);

        expect($notification->body)->toBe('Hello there')
            ->and($notification->sender_user_id)->toBe($sender->id);
        $this->assertDatabaseHas('notification_threads', [
            'id' => $notification->notification_thread_id,
            'type' => NotificationThreadTypeEnum::GENERIC->value,
        ]);
        $this->assertDatabaseHas('thread_members', ['notification_thread_id' => $notification->notification_thread_id, 'user_id' => $recipient->id, 'is_read' => false]);
        $this->assertDatabaseHas('thread_members', ['notification_thread_id' => $notification->notification_thread_id, 'user_id' => $sender->id, 'is_read' => true]);
        expect(ThreadMember::query()->where('notification_thread_id', $notification->notification_thread_id)->count())->toBe(2);
    });

    // --- WarningNotificationService ---

    it('creates a warning notification for a recipient under a titled thread', function (): void {
        $recipient = User::factory()->create();

        resolve(WarningNotificationService::class)->createNotification($recipient->id, 'Maintenance tonight', 'Heads up');

        $this->assertDatabaseHas('notification_threads', [
            'type' => NotificationThreadTypeEnum::WARNING->value,
            'title' => 'Heads up',
        ]);
        expect(Notification::query()->where('body', 'Maintenance tonight')->count())->toBe(1)
            ->and(ThreadMember::query()->count())->toBe(1);
    });

    // --- InfoNotificationService ---

    it('creates an info notification for a recipient under a titled thread', function (): void {
        $recipient = User::factory()->create();

        resolve(InfoNotificationService::class)->createNotification($recipient->id, 'New feature shipped', 'FYI');

        expect(NotificationThread::query()->where('type', NotificationThreadTypeEnum::INFO)->count())->toBe(1)
            ->and(Notification::query()->where('body', 'New feature shipped')->count())->toBe(1);
    });

    // --- FlagNotificationService ---

    it('creates a flag notification with two quick links', function (): void {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();

        $notification = resolve(FlagNotificationService::class)->createNotification(
            recipientUserIds: [$recipient->id],
            body: 'Flagged item',
            senderUserId: $sender->id,
            firstQuickLink: new QuickLinkData('First', '/first'),
            secondQuickLink: new QuickLinkData('Second', '/second'),
        );

        $this->assertDatabaseHas('notification_threads', [
            'id' => $notification->notification_thread_id,
            'type' => NotificationThreadTypeEnum::FLAG_NOTIFICATION->value,
        ]);
        expect(QuickLink::query()->where('notification_thread_id', $notification->notification_thread_id)->count())->toBe(2);
        $this->assertDatabaseHas('quick_links', ['label' => 'First', 'url' => '/first']);
        $this->assertDatabaseHas('quick_links', ['label' => 'Second', 'url' => '/second']);
    });

    // --- InstanceRelatedNotificationService ---

    it('creates an instance-related notification with two quick links', function (): void {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();

        $notification = resolve(InstanceRelatedNotificationService::class)->createNotification(
            recipientUserIds: [$recipient->id],
            body: 'Instance note',
            senderUserId: $sender->id,
            firstQuickLink: new QuickLinkData('Open', '/open'),
            secondQuickLink: new QuickLinkData('Resolve', '/resolve'),
        );

        $this->assertDatabaseHas('notification_threads', [
            'id' => $notification->notification_thread_id,
            'type' => NotificationThreadTypeEnum::INSTANCE_RELATED->value,
        ]);
        expect(QuickLink::query()->where('notification_thread_id', $notification->notification_thread_id)->count())->toBe(2);
    });

    // --- AnnouncementNotificationService ---

    it('creates one announcement notification with one quick link shared across all recipients', function (): void {
        $recipients = User::factory()->count(2)->create();
        $sender = User::factory()->create();

        resolve(AnnouncementNotificationService::class)->createNotification(
            recipientUserIds: $recipients->pluck('id')->map(fn ($id): int => (int) $id)->all(),
            body: 'Big news',
            senderUserId: $sender->id,
            quickLink: new QuickLinkData('Read more', '/news'),
        );

        $thread = NotificationThread::query()->where('type', NotificationThreadTypeEnum::ANNOUNCEMENT)->firstOrFail();
        expect(Notification::query()->where('notification_thread_id', $thread->id)->count())->toBe(1)
            ->and(ThreadMember::query()->where('notification_thread_id', $thread->id)->count())->toBe(2)
            ->and(QuickLink::query()->where('notification_thread_id', $thread->id)->count())->toBe(1);
    });

    // --- ProjectOwnershipNotificationService ---

    it('creates a project ownership notification with a thread response and quick link', function (): void {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $project = Project::factory()->create(['owner_user_id' => $sender->id]);

        $notification = resolve(ProjectOwnershipNotificationService::class)->createNotification(
            recipientUserId: $recipient->id,
            senderUserId: $sender->id,
            body: 'Ownership offer',
            quickLink: new QuickLinkData('Decide', '/decide'),
            projectId: $project->id,
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

    // --- ProjectInvitationNotificationService ---

    it('creates a project invitation notification with a thread response and quick link', function (): void {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $project = Project::factory()->create(['owner_user_id' => $sender->id]);

        $notification = resolve(ProjectInvitationNotificationService::class)->createNotification(
            recipientUserId: $recipient->id,
            senderUserId: $sender->id,
            body: 'Join the project',
            quickLink: new QuickLinkData('Accept', '/accept'),
            projectId: $project->id,
        );

        $this->assertDatabaseHas('notification_threads', [
            'id' => $notification->notification_thread_id,
            'type' => NotificationThreadTypeEnum::PROJECT_INVITATION->value,
        ]);
        $this->assertDatabaseHas('notification_thread_responses', [
            'notification_thread_id' => $notification->notification_thread_id,
        ]);
    });

    // --- GenericNotificationService::reply() ---

    it('replies to a generic thread without creating a new thread', function (): void {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();
        $genericService = resolve(GenericNotificationService::class);
        $original = $genericService->createNotification($recipient->id, 'First message', $sender->id);
        $threadCountBefore = NotificationThread::query()->count();

        $reply = $genericService->reply(
            $original->notification_thread_id,
            $recipient->id,
            'A reply',
        );

        expect($reply->notification_thread_id)->toBe($original->notification_thread_id)
            ->and(NotificationThread::query()->count())->toBe($threadCountBefore)
            ->and(Notification::query()->where('notification_thread_id', $original->notification_thread_id)->count())->toBe(2);
    });

    // --- mark / unread delegators ---

    it('marks a thread membership as read and then unread', function (): void {
        $user = User::factory()->create();
        $thread = NotificationThread::factory()->create();
        Notification::factory()->create(['notification_thread_id' => $thread->id]);
        ThreadMember::factory()->create(['notification_thread_id' => $thread->id, 'user_id' => $user->id, 'is_read' => false]);

        $this->service->markAsRead($thread->id, $user->id);
        $this->assertDatabaseHas('thread_members', ['user_id' => $user->id, 'is_read' => true]);

        $this->service->markAsUnread($thread->id, $user->id);
        $this->assertDatabaseHas('thread_members', ['user_id' => $user->id, 'is_read' => false]);
    });

    it("marks all of a user's memberships as read", function (): void {
        $user = User::factory()->create();
        foreach (range(1, 2) as $i) {
            $thread = NotificationThread::factory()->create();
            Notification::factory()->create(['notification_thread_id' => $thread->id]);
            ThreadMember::factory()->create(['notification_thread_id' => $thread->id, 'user_id' => $user->id, 'is_read' => false]);
        }

        $this->service->markAllAsRead($user->id);

        $this->assertDatabaseMissing('thread_members', ['user_id' => $user->id, 'is_read' => false]);
    });

    // --- hasUnreadNotifications() ---

    it('reports whether a user has any unread notifications', function (): void {
        $user = User::factory()->create();
        $thread = NotificationThread::factory()->create();
        Notification::factory()->create(['notification_thread_id' => $thread->id]);
        $member = ThreadMember::factory()->create(['notification_thread_id' => $thread->id, 'user_id' => $user->id, 'is_read' => false]);

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

        expect($this->service->getMyNotifications($user))->toHaveCount(0);
    });

    it('exposes a generic thread as repliable and titles it by the sender', function (): void {
        $user = User::factory()->create();
        $sender = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);
        Notification::factory()->create([
            'notification_thread_id' => $thread->id,
            'sender_user_id' => $sender->id,
        ]);
        ThreadMember::factory()->create(['notification_thread_id' => $thread->id, 'user_id' => $user->id, 'is_read' => false]);

        $found = $this->service->getMyNotifications($user)->firstWhere('id', $thread->id);

        expect($found->allowed_to_reply)->toBeTrue()
            ->and($found->top_right)->toBeNull()
            ->and($found->replied_by)->toBeNull()
            ->and($found->title)->toBe($sender->username)
            ->and($found->is_read)->toBeFalse();
    });

    it('shows the first quick link as top_right for a flag thread and allows replies', function (): void {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::FLAG_NOTIFICATION]);
        Notification::factory()->create(['notification_thread_id' => $thread->id, 'sender_user_id' => $sender->id]);
        ThreadMember::factory()->create(['notification_thread_id' => $thread->id, 'user_id' => $user->id]);
        QuickLink::factory()->create(['notification_thread_id' => $thread->id, 'label' => 'Go to instance']);

        $found = $this->service->getMyNotifications($user)->firstWhere('id', $thread->id);

        expect($found->allowed_to_reply)->toBeTrue()
            ->and($found->top_right)->toBe('Go to instance');
    });

    it('shows the quick link as top_right for an announcement but does not allow replies', function (): void {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::ANNOUNCEMENT]);
        Notification::factory()->create(['notification_thread_id' => $thread->id, 'sender_user_id' => $sender->id]);
        ThreadMember::factory()->create(['notification_thread_id' => $thread->id, 'user_id' => $user->id]);
        QuickLink::factory()->create(['notification_thread_id' => $thread->id, 'label' => 'Announcement link']);

        $found = $this->service->getMyNotifications($user)->firstWhere('id', $thread->id);

        expect($found->allowed_to_reply)->toBeFalse()
            ->and($found->top_right)->toBe('Announcement link')
            ->and($found->title)->toBe($sender->username);
    });

    it('exposes the response value and Ownership top_right for a project ownership thread', function (): void {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::PROJECT_OWNERSHIP]);
        Notification::factory()->create(['notification_thread_id' => $thread->id, 'sender_user_id' => $sender->id]);
        ThreadMember::factory()->create(['notification_thread_id' => $thread->id, 'user_id' => $user->id]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::UNREPLIED,
        ]);

        $found = $this->service->getMyNotifications($user)->firstWhere('id', $thread->id);

        expect($found->top_right)->toBe('Ownership')
            ->and($found->response)->toBe(NotificationThreadResponseEnum::UNREPLIED->value);
    });

    it('shows the Invitation to Project top_right for a project invitation thread', function (): void {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::PROJECT_INVITATION]);
        Notification::factory()->create(['notification_thread_id' => $thread->id, 'sender_user_id' => $sender->id]);
        ThreadMember::factory()->create(['notification_thread_id' => $thread->id, 'user_id' => $user->id]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::ACCEPTED,
        ]);

        $found = $this->service->getMyNotifications($user)->firstWhere('id', $thread->id);

        expect($found->top_right)->toBe('Invitation to Project')
            ->and($found->response)->toBe(NotificationThreadResponseEnum::ACCEPTED->value);
    });

    it('does not set a title and disallows replies for warning threads', function (): void {
        $user = User::factory()->create();
        $thread = NotificationThread::factory()->create([
            'type' => NotificationThreadTypeEnum::WARNING,
            'title' => 'Original warning title',
        ]);
        Notification::factory()->create(['notification_thread_id' => $thread->id]);
        ThreadMember::factory()->create(['notification_thread_id' => $thread->id, 'user_id' => $user->id]);

        $found = $this->service->getMyNotifications($user)->firstWhere('id', $thread->id);

        expect($found->allowed_to_reply)->toBeFalse()
            ->and($found->top_right)->toBeNull()
            ->and($found->title)->toBe('Original warning title');
    });

    it('marks a thread as read when every membership is read', function (): void {
        $user = User::factory()->create();
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);
        Notification::factory()->create(['notification_thread_id' => $thread->id]);
        ThreadMember::factory()->create(['notification_thread_id' => $thread->id, 'user_id' => $user->id, 'is_read' => true]);

        $found = $this->service->getMyNotifications($user)->firstWhere('id', $thread->id);

        expect($found->is_read)->toBeTrue();
    });

    it('reports replied_by and transformed notification attributes for multi-notification threads', function (): void {
        $user = User::factory()->create();
        $sender = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);

        Notification::factory()->create([
            'notification_thread_id' => $thread->id,
            'sender_user_id' => $user->id,
            'created_at' => now()->subMinutes(5),
        ]);
        $latest = Notification::factory()->create([
            'notification_thread_id' => $thread->id,
            'sender_user_id' => $sender->id,
            'created_at' => now(),
        ]);
        ThreadMember::factory()->create(['notification_thread_id' => $thread->id, 'user_id' => $user->id]);

        $found = $this->service->getMyNotifications($user)->firstWhere('id', $thread->id);

        expect($found->replied_by)->toBe($sender->username);
        $transformed = $found->notifications->firstWhere('id', $latest->id);
        expect($transformed->sender_username)->toBe($sender->username)
            ->and($transformed->sender_role)->toBe(RolesEnum::ANNOTATOR->value)
            ->and($transformed->datetime)->not->toBeNull();
    });

    it('returns threads sorted by most recent activity first', function (): void {
        $user = User::factory()->create();

        $older = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);
        Notification::factory()->create(['notification_thread_id' => $older->id, 'created_at' => now()->subDays(2)]);
        ThreadMember::factory()->create(['notification_thread_id' => $older->id, 'user_id' => $user->id]);

        $newer = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);
        Notification::factory()->create(['notification_thread_id' => $newer->id, 'created_at' => now()]);
        ThreadMember::factory()->create(['notification_thread_id' => $newer->id, 'user_id' => $user->id]);

        $result = $this->service->getMyNotifications($user);

        expect($result->first()->id)->toBe($newer->id)
            ->and($result->last()->id)->toBe($older->id);
    });

    // --- approve() / reject() delegation ---

    it('approves an actionable thread by delegating to the matching type service', function (): void {
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::PROJECT_OWNERSHIP]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::UNREPLIED,
        ]);

        $this->service->approve($thread->id);

        $this->assertDatabaseHas('notification_thread_responses', [
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::ACCEPTED->value,
        ]);
    });

    it('rejects an actionable thread by delegating to the matching type service', function (): void {
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::PROJECT_OWNERSHIP]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::UNREPLIED,
        ]);

        $this->service->reject($thread->id);

        $this->assertDatabaseHas('notification_thread_responses', [
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::REJECTED->value,
        ]);
    });

    it('throws a model-not-found error when approving a missing thread', function (): void {
        expect(function (): void {
            $this->service->approve(999999);
        })->toThrow(ModelNotFoundException::class);
    });

    it('aborts with 403 when approving a non-actionable thread type', function (): void {
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);

        expect(function () use ($thread): void {
            $this->service->approve($thread->id);
        })->toThrow(HttpException::class);
    });
});
