<?php

declare(strict_types=1);

use App\Enums\NotificationThreadResponseEnum;
use App\Enums\NotificationThreadTypeEnum;
use App\Enums\RolesEnum;
use App\Models\NotificationThread;
use App\Models\NotificationThreadResponse;
use App\Models\ThreadMember;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * Builds a thread with a membership for the given user,
 * returning the thread so callers can hit the read/unread routes by thread id.
 */
function makeThreadFor(User $user, bool $isRead): NotificationThread {
    $thread = NotificationThread::factory()->create();
    ThreadMember::factory()->create([
        'notification_thread_id' => $thread->id,
        'user_id' => $user->id,
        'is_read' => $isRead,
    ]);

    return $thread;
}

describe('NotificationController::index', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('redirects guests to the login page', function (): void {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    });

    it('renders the notifications page with the threads prop', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('notifications/index')
                ->has('threads'));
    });

    it("includes the authenticated user's own thread in the payload", function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        makeThreadFor($user, isRead: false);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('notifications/index')
                ->has('threads', 1));
    });
});

describe('NotificationController::markAsRead', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('redirects guests to the login page', function (): void {
        $this->post(route('notifications.read', ['notificationThreadId' => 1]))
            ->assertRedirect(route('login'));
    });

    it("marks the authenticated user's membership as read and returns ok", function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = makeThreadFor($user, isRead: false);

        // Act
        $response = $this->actingAs($user)
            ->post(route('notifications.read', ['notificationThreadId' => $thread->id]));

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('thread_members', [
            'user_id' => $user->id,
            'is_read' => true,
        ]);
    });

    it("does not touch other users' membership on the same thread", function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $other = User::factory()->create();
        $thread = NotificationThread::factory()->create();
        ThreadMember::factory()->create([
            'notification_thread_id' => $thread->id,
            'user_id' => $user->id,
            'is_read' => false,
        ]);
        $otherMember = ThreadMember::factory()->create([
            'notification_thread_id' => $thread->id,
            'user_id' => $other->id,
            'is_read' => false,
        ]);

        // Act
        $this->actingAs($user)
            ->post(route('notifications.read', ['notificationThreadId' => $thread->id]));

        // Assert
        $this->assertDatabaseHas('thread_members', [
            'user_id' => $user->id,
            'is_read' => true,
        ]);
        $this->assertDatabaseHas('thread_members', [
            'id' => $otherMember->id,
            'is_read' => false,
        ]);
    });
});

describe('NotificationController::markAsUnread', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('redirects guests to the login page', function (): void {
        $this->post(route('notifications.unread', ['notificationThreadId' => 1]))
            ->assertRedirect(route('login'));
    });

    it("marks the authenticated user's membership as unread and returns ok", function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = makeThreadFor($user, isRead: true);

        // Act
        $response = $this->actingAs($user)
            ->post(route('notifications.unread', ['notificationThreadId' => $thread->id]));

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('thread_members', [
            'user_id' => $user->id,
            'is_read' => false,
        ]);
    });
});

describe('NotificationController::markAllAsRead', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('redirects guests to the login page', function (): void {
        $this->post(route('notifications.read-all'))
            ->assertRedirect(route('login'));
    });

    it("marks all the authenticated user's memberships as read and returns ok", function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        makeThreadFor($user, isRead: false);
        makeThreadFor($user, isRead: false);

        // Act
        $response = $this->actingAs($user)
            ->post(route('notifications.read-all'));

        // Assert
        $response->assertOk();
        $this->assertDatabaseMissing('thread_members', [
            'user_id' => $user->id,
            'is_read' => false,
        ]);
    });

    it("does not touch another user's memberships", function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $other = User::factory()->create();
        makeThreadFor($user, isRead: false);
        $otherThread = makeThreadFor($other, isRead: false);
        $otherMember = ThreadMember::query()
            ->where('user_id', $other->id)
            ->where('notification_thread_id', $otherThread->id)
            ->firstOrFail();

        // Act
        $this->actingAs($user)
            ->post(route('notifications.read-all'));

        // Assert
        $this->assertDatabaseHas('thread_members', [
            'id' => $otherMember->id,
            'is_read' => false,
        ]);
    });
});

describe('NotificationController::reply', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('redirects guests to the login page', function (): void {
        $this->post(route('notifications.reply', ['notificationThreadId' => 1]), ['body' => 'Hi'])
            ->assertRedirect(route('login'));
    });

    it('persists the reply and returns the created message as JSON', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);

        // Act
        $response = $this->actingAs($user)
            ->postJson(route('notifications.reply', ['notificationThreadId' => $thread->id]), [
                'body' => 'My reply',
            ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('notification.body', 'My reply')
            ->assertJsonPath('notification.sender_user_id', $user->id)
            ->assertJsonPath('notification.sender_username', $user->username)
            ->assertJsonStructure(['notification' => ['id', 'datetime', 'sender_role'], 'success']);
        $this->assertDatabaseHas('notifications', [
            'notification_thread_id' => $thread->id,
            'sender_user_id' => $user->id,
            'body' => 'My reply',
        ]);
    });

    it('rejects an empty body with a validation error', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);

        // Act & Assert
        $this->actingAs($user)
            ->postJson(route('notifications.reply', ['notificationThreadId' => $thread->id]), ['body' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');
    });
});

describe('NotificationController::sendMessage', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('redirects guests to the login page', function (): void {
        $this->post(route('notifications.send'), ['recipient_user_id' => 1, 'body' => 'Hi'])
            ->assertRedirect(route('login'));
    });

    it('delivers a generic message to the recipient with both thread members', function (): void {
        // Arrange
        $sender = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $recipient = User::factory()->create();

        // Act
        $response = $this->actingAs($sender)
            ->postJson(route('notifications.send'), [
                'recipient_user_id' => $recipient->id,
                'body' => '  Hello there  ',
            ]);

        // Assert
        $response->assertOk();

        $thread = NotificationThread::query()
            ->where('type', NotificationThreadTypeEnum::GENERIC->value)
            ->firstOrFail();
        $this->assertDatabaseHas('notifications', [
            'notification_thread_id' => $thread->id,
            'sender_user_id' => $sender->id,
            'body' => 'Hello there',
        ]);
        $this->assertDatabaseHas('thread_members', ['notification_thread_id' => $thread->id, 'user_id' => $sender->id, 'is_read' => true]);
        $this->assertDatabaseHas('thread_members', ['notification_thread_id' => $thread->id, 'user_id' => $recipient->id, 'is_read' => false]);
    });

    it('rejects a missing recipient with a validation error', function (): void {
        $sender = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);

        $this->actingAs($sender)
            ->postJson(route('notifications.send'), ['body' => 'Hello'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('recipient_user_id');
    });

    it('rejects a non-existent recipient with a validation error', function (): void {
        $sender = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);

        $this->actingAs($sender)
            ->postJson(route('notifications.send'), ['recipient_user_id' => 999999, 'body' => 'Hello'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('recipient_user_id');
    });

    it('rejects a missing body with a validation error', function (): void {
        $sender = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $recipient = User::factory()->create();

        $this->actingAs($sender)
            ->postJson(route('notifications.send'), ['recipient_user_id' => $recipient->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');
    });

    it('rejects a body longer than 1000 characters with a validation error', function (): void {
        $sender = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $recipient = User::factory()->create();

        $this->actingAs($sender)
            ->postJson(route('notifications.send'), [
                'recipient_user_id' => $recipient->id,
                'body' => str_repeat('a', 1001),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');
    });
});

describe('NotificationController::approve', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('redirects guests to the login page', function (): void {
        $this->post(route('notifications.approve', ['notificationThreadId' => 1]))
            ->assertRedirect(route('login'));
    });

    it('approves an ownership thread and marks the response accepted', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::PROJECT_OWNERSHIP]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::UNREPLIED,
        ]);

        // Act
        $response = $this->actingAs($user)
            ->postJson(route('notifications.approve', ['notificationThreadId' => $thread->id]));

        // Assert
        $response->assertOk()
            ->assertJsonPath('success', __('notifications.action_approved'));
        $this->assertDatabaseHas('notification_thread_responses', [
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::ACCEPTED->value,
        ]);
    });

    it('returns a presentable error when the thread was already rejected', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::PROJECT_OWNERSHIP]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::REJECTED,
        ]);

        // Act & Assert
        $this->actingAs($user)
            ->postJson(route('notifications.approve', ['notificationThreadId' => $thread->id]))
            ->assertStatus(422)
            ->assertJsonPath('error', __('notifications.errors.cannot_approve_rejected'));
    });

    it('forbids approving a non-actionable thread type', function (): void {
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::GENERIC]);

        $this->actingAs($user)
            ->postJson(route('notifications.approve', ['notificationThreadId' => $thread->id]))
            ->assertStatus(403);
    });

    it('returns 404 when the thread does not exist', function (): void {
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);

        $this->actingAs($user)
            ->postJson(route('notifications.approve', ['notificationThreadId' => 999999]))
            ->assertStatus(404);
    });
});

describe('NotificationController::reject', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('redirects guests to the login page', function (): void {
        $this->post(route('notifications.reject', ['notificationThreadId' => 1]))
            ->assertRedirect(route('login'));
    });

    it('rejects an invitation thread and marks the response rejected', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::PROJECT_INVITATION]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::UNREPLIED,
        ]);

        // Act
        $response = $this->actingAs($user)
            ->postJson(route('notifications.reject', ['notificationThreadId' => $thread->id]));

        // Assert
        $response->assertOk()
            ->assertJsonPath('success', __('notifications.action_rejected'));
        $this->assertDatabaseHas('notification_thread_responses', [
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::REJECTED->value,
        ]);
    });

    it('returns a presentable error when the thread was already accepted', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = NotificationThread::factory()->create(['type' => NotificationThreadTypeEnum::PROJECT_INVITATION]);
        NotificationThreadResponse::factory()->create([
            'notification_thread_id' => $thread->id,
            'response' => NotificationThreadResponseEnum::ACCEPTED,
        ]);

        // Act & Assert
        $this->actingAs($user)
            ->postJson(route('notifications.reject', ['notificationThreadId' => $thread->id]))
            ->assertStatus(422)
            ->assertJsonPath('error', __('notifications.errors.cannot_reject_accepted'));
    });
});
