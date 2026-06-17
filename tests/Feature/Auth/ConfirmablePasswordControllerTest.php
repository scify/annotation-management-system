<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('ConfirmablePasswordController::show', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('redirects guests to the login page', function (): void {
        $this->get(route('password.confirm'))->assertRedirect(route('login'));
    });

    it('renders the confirm-password page for authenticated users', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('password.confirm'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('auth/confirm-password'));
    });
});

describe('ConfirmablePasswordController::store', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('confirms the password with correct credentials and redirects to the intended page', function (): void {
        // Arrange — the factory default password is "password".
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->from(route('password.confirm'))
            ->post(route('password.confirmation'), ['password' => 'password']);

        // Assert
        $response->assertRedirect(route('dashboard'));

        expect(session('auth.password_confirmed_at'))->not->toBeNull();
    });

    it('rejects an incorrect password', function (): void {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->from(route('password.confirm'))
            ->post(route('password.confirmation'), ['password' => 'wrong-password']);

        // Assert
        $response->assertRedirect(route('password.confirm'))
            ->assertSessionHasErrors('password');
        expect(session('auth.password_confirmed_at'))->toBeNull();
    });
});
