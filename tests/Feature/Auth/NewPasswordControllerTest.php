<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

describe('NewPasswordController::create', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('renders the reset-password page with the email and token', function (): void {
        $this->get(route('password.reset', ['token' => 'tok-123']) . '?email=jane%40example.com')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('auth/reset-password')
                ->where('token', 'tok-123')
                ->where('email', 'jane@example.com'));
    });
});

describe('NewPasswordController::store', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('resets the password with a valid token and redirects to login', function (): void {
        // Arrange
        Event::fake([PasswordReset::class]);
        $user = User::factory()->create();
        $token = Password::createToken($user);

        // Act
        $response = $this->from(route('password.reset', ['token' => $token]))
            ->post(route('password.store'), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        // Assert
        $response->assertRedirect(route('login'))
            ->assertSessionHas('status');
        expect(Hash::check('NewPassword123', $user->fresh()->password))->toBeTrue();
        Event::assertDispatched(PasswordReset::class);
    });

    it('rejects an invalid token and leaves the password unchanged', function (): void {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->from(route('password.reset', ['token' => 'invalid']))
            ->post(route('password.store'), [
                'token' => 'invalid-token',
                'email' => $user->email,
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        // Assert
        $response->assertSessionHasErrors('email');

        expect(Hash::check('NewPassword123', $user->fresh()->password))->toBeFalse();
    });

    it('validates the token, email, and password', function (): void {
        // Act
        $response = $this->from(route('password.store'))
            ->post(route('password.store'), [
                'token' => '',
                'email' => 'not-an-email',
                'password' => 'short',
            ]);

        // Assert
        $response->assertSessionHasErrors(['token', 'email', 'password']);
    });
});
