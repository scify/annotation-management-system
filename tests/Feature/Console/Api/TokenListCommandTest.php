<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('api:token:list', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('lists tokens for a user by name', function (): void {
        // Arrange
        $user = User::factory()->create(['email' => 'service@example.com']);
        $user->createToken('integration-a');
        $user->createToken('integration-b');

        // Act & Assert
        $this->artisan('api:token:list', ['email' => 'service@example.com'])
            ->assertExitCode(0)
            ->expectsOutputToContain('integration-a')
            ->expectsOutputToContain('integration-b');
    });

    it('shows an informational message when user has no tokens', function (): void {
        // Arrange
        User::factory()->create(['email' => 'service@example.com']);

        // Act & Assert
        $this->artisan('api:token:list', ['email' => 'service@example.com'])
            ->assertExitCode(0)
            ->expectsOutputToContain('has no personal access tokens');
    });

    it('exits 1 with an error for an unknown email', function (): void {
        $this->artisan('api:token:list', ['email' => 'ghost@example.com'])
            ->assertExitCode(1)
            ->expectsOutputToContain('No active user found');
    });
});
