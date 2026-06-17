<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

describe('PasswordResetLinkController::create', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('renders the forgot-password page', function (): void {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('auth/forgot-password'));
    });
});

describe('PasswordResetLinkController::store', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        Notification::fake();
    });

    it('sends a reset link to an existing user and redirects back with a status', function (): void {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email]);

        // Assert
        $response->assertRedirect(route('password.request'))
            ->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    });

    it('does not reveal whether the email exists (no notification, generic status)', function (): void {
        // Act
        $response = $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'nobody@example.com']);

        // Assert
        $response->assertRedirect(route('password.request'))
            ->assertSessionHas('status');
        Notification::assertNothingSent();
    });

    it('validates that a well-formed email is provided', function (): void {
        // Act
        $response = $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'not-an-email']);

        // Assert
        $response->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');
        Notification::assertNothingSent();
    });
});
