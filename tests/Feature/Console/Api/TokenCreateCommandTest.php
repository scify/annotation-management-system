<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\PersonalAccessToken;

describe('api:token:create', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('creates a token for a valid user and shows the plain-text token', function (): void {
        // Arrange
        $user = User::factory()->create(['email' => 'service@example.com']);

        // Act & Assert
        $this->artisan('api:token:create', ['email' => 'service@example.com'])
            ->assertExitCode(0)
            ->expectsOutputToContain('Plain-text token');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'default',
        ]);
    });

    it('persists a custom --name', function (): void {
        // Arrange
        User::factory()->create(['email' => 'service@example.com']);

        // Act
        $this->artisan('api:token:create', [
            'email' => 'service@example.com',
            '--name' => 'my-integration',
        ])->assertExitCode(0);

        // Assert
        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'my-integration']);
    });

    it('stores custom --abilities', function (): void {
        // Arrange
        User::factory()->create(['email' => 'service@example.com']);

        // Act
        $this->artisan('api:token:create', [
            'email' => 'service@example.com',
            '--abilities' => ['read', 'write'],
        ])->assertExitCode(0);

        // Assert
        $token = PersonalAccessToken::query()->where('name', 'default')->firstOrFail();
        expect($token->abilities)->toBe(['read', 'write']);
    });

    it('exits 1 with an error for an unknown email', function (): void {
        $this->artisan('api:token:create', ['email' => 'ghost@example.com'])
            ->assertExitCode(1)
            ->expectsOutputToContain('No active user found');
    });

    it('exits 1 for a soft-deleted user', function (): void {
        // Arrange
        $user = User::factory()->create(['email' => 'deleted@example.com']);
        $user->delete();

        // Act & Assert
        $this->artisan('api:token:create', ['email' => 'deleted@example.com'])
            ->assertExitCode(1)
            ->expectsOutputToContain('No active user found');
    });
});
