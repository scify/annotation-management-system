<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\NotificationThread;
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

    it("marks the authenticated user's membership as read and redirects back", function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = makeThreadFor($user, isRead: false);

        // Act
        $response = $this->actingAs($user)
            ->from(route('notifications.index'))
            ->post(route('notifications.read', ['notificationThreadId' => $thread->id]));

        // Assert
        $response->assertRedirect(route('notifications.index'));
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

    it("marks the authenticated user's membership as unread and redirects back", function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $thread = makeThreadFor($user, isRead: true);

        // Act
        $response = $this->actingAs($user)
            ->from(route('notifications.index'))
            ->post(route('notifications.unread', ['notificationThreadId' => $thread->id]));

        // Assert
        $response->assertRedirect(route('notifications.index'));
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

    it("marks all the authenticated user's memberships as read and redirects back", function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        makeThreadFor($user, isRead: false);
        makeThreadFor($user, isRead: false);

        // Act
        $response = $this->actingAs($user)
            ->from(route('notifications.index'))
            ->post(route('notifications.read-all'));

        // Assert
        $response->assertRedirect(route('notifications.index'));
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
