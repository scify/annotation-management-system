<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\User;
use App\Notifications\UserWelcomeNotification;
use Database\Seeders\AnnotatorPasswordPolicySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

describe('UserWelcomeNotification', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(AnnotatorPasswordPolicySeeder::class);
        $this->faker = Factory::create();

        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User',
        ])->assignRole(RolesEnum::ADMIN->value)->load('roles');

        $this->annotationManager = User::factory()->create([
            'email' => 'annotation_manager@example.com',
            'name' => 'Annotation Manager',
        ])->assignRole(RolesEnum::ANNOTATION_MANAGER->value)->load('roles');
    });

    it('sends a welcome email to a newly created annotation manager', function (): void {
        // Arrange
        Notification::fake();
        $this->actingAs($this->admin)->get(route('users.create', ['type' => RolesEnum::ANNOTATION_MANAGER->value]));
        $username = $this->faker->unique()->userName();

        // Act
        $this->post(route('users.store'), [
            'type' => RolesEnum::ANNOTATION_MANAGER->value,
            'name' => 'New Manager',
            'username' => $username,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'annotation_task_ids' => [],
            'dataset_ids' => [],
            'project_ids' => [],
            'annotator_ids' => [],
            '_token' => session('_token'),
        ])->assertRedirect(route('users.index'));

        // Assert
        $manager = User::query()->where('username', $username)->firstOrFail();

        Notification::assertSentTo(
            $manager,
            UserWelcomeNotification::class,
            fn (UserWelcomeNotification $notification): bool => $notification->toMail($manager)->viewData['creatorName'] === $this->admin->name
        );
    });

    it('does not send a welcome email when creating an annotator', function (): void {
        // Arrange
        Notification::fake();
        $this->actingAs($this->admin)->get(route('users.create', ['type' => RolesEnum::ANNOTATOR->value]));
        $username = $this->faker->unique()->userName();

        // Act
        $this->post(route('users.store'), [
            'type' => RolesEnum::ANNOTATOR->value,
            'name' => 'Test Annotator',
            'username' => $username,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'manager_ids' => [$this->annotationManager->id],
            '_token' => session('_token'),
        ])->assertRedirect(route('users.index'));

        // Assert
        $annotator = User::query()->where('username', $username)->firstOrFail();

        Notification::assertNothingSentTo($annotator);
    });

    it('builds mail content naming the creator and the assigned role', function (): void {
        // Arrange
        $creator = new User(['name' => 'Jane Creator']);
        $recipient = new User(['name' => 'New Manager']);
        $notification = new UserWelcomeNotification($creator, RolesEnum::ANNOTATION_MANAGER);

        // Act
        $mail = $notification->toMail($recipient);

        // Assert
        expect($mail->viewData['creatorName'])->toBe('Jane Creator')
            ->and($mail->viewData['roleName'])->toBe(__('roles.' . RolesEnum::ANNOTATION_MANAGER->value))
            ->and($mail->viewData['name'])->toBe('New Manager');
    });

    it('falls back to a generic creator name when no creator is provided', function (): void {
        // Arrange
        $recipient = new User(['name' => 'New Admin']);
        $notification = new UserWelcomeNotification(null, RolesEnum::ADMIN);

        // Act
        $mail = $notification->toMail($recipient);

        // Assert
        expect($mail->viewData['creatorName'])->toBe(__('emails.welcome.creator_fallback'));
    });
});
