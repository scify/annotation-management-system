<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('Bearer token authentication', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('authenticates a valid bearer token', function (): void {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('test-client');

        // Act
        $response = $this->withToken($token->plainTextToken)
            ->getJson('/api/v1/user/info');

        // Assert
        $response->assertOk()
            ->assertJsonPath('user.id', $user->id);
    });

    it('returns 401 for an invalid bearer token', function (): void {
        // Act
        $response = $this->withToken('invalid-token-value')
            ->getJson('/api/v1/user/info');

        // Assert
        $response->assertUnauthorized();
    });
});
