<?php

declare(strict_types=1);

use App\Enums\NotificationThreadTypeEnum;
use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\User;
use App\Notifications\CoManagerInvitationNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Notification;

describe('ProjectController::inviteManager', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        Notification::fake();

        $this->owner = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER)->load('roles');
        $this->project = Project::factory()->create(['owner_user_id' => $this->owner->id]);
        ProjectManager::query()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->owner->id,
            'accepted' => true,
        ]);
    });

    it('invites an eligible co-manager by email and sends the notification', function (): void {
        // Arrange
        $invitee = User::factory()->create(['status' => StatusEnum::ACTIVE])
            ->assignRole(RolesEnum::ANNOTATION_MANAGER)->load('roles');

        // Act
        $response = $this->actingAs($this->owner)
            ->postJson(route('projects.invite-manager', $this->project->id), ['email' => $invitee->email]);

        // Assert
        $response->assertOk()->assertJsonStructure(['success']);
        Notification::assertSentTo($invitee, CoManagerInvitationNotification::class);
        $this->assertDatabaseHas('notification_threads', [
            'type' => NotificationThreadTypeEnum::PROJECT_INVITATION->value,
        ]);
        // Notification-only: the invitee is NOT added to the project until they accept.
        $this->assertDatabaseMissing('project_managers', [
            'project_id' => $this->project->id,
            'user_id' => $invitee->id,
        ]);
    });

    it('returns 422 when the user is already a co-manager of the project', function (): void {
        // Arrange
        $existing = User::factory()->create(['status' => StatusEnum::ACTIVE])
            ->assignRole(RolesEnum::ANNOTATION_MANAGER)->load('roles');
        ProjectManager::query()->create([
            'project_id' => $this->project->id,
            'user_id' => $existing->id,
            'accepted' => true,
        ]);

        // Act
        $response = $this->actingAs($this->owner)
            ->postJson(route('projects.invite-manager', $this->project->id), ['email' => $existing->email]);

        // Assert
        $response->assertStatus(422)->assertJsonStructure(['error']);
        Notification::assertNothingSent();
    });

    it('returns 404 when no eligible co-manager exists for the email', function (): void {
        // Arrange — an annotator is not an eligible co-manager
        $annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR)->load('roles');

        // Act
        $response = $this->actingAs($this->owner)
            ->postJson(route('projects.invite-manager', $this->project->id), ['email' => $annotator->email]);

        // Assert
        $response->assertStatus(404)->assertJsonStructure(['error']);
        Notification::assertNothingSent();
    });

    it('returns 404 for an unknown email', function (): void {
        // Act
        $response = $this->actingAs($this->owner)
            ->postJson(route('projects.invite-manager', $this->project->id), ['email' => 'nobody@example.com']);

        // Assert
        $response->assertStatus(404);
    });

    it('forbids a non-owner, non-admin manager from inviting', function (): void {
        // Arrange
        $stranger = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER)->load('roles');
        $invitee = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER)->load('roles');

        // Act
        $response = $this->actingAs($stranger)
            ->postJson(route('projects.invite-manager', $this->project->id), ['email' => $invitee->email]);

        // Assert
        $response->assertForbidden();
        Notification::assertNothingSent();
    });

    it('validates that an email is required', function (): void {
        $this->actingAs($this->owner)
            ->postJson(route('projects.invite-manager', $this->project->id), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    });
});
