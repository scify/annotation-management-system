<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('api:token:revoke', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('deletes the token and confirms revocation', function (): void {
        // Arrange
        $user = User::factory()->create(['email' => 'service@example.com']);
        $newToken = $user->createToken('my-app');
        $tokenId = $newToken->accessToken->id;

        // Act & Assert
        $this->artisan('api:token:revoke', ['tokenId' => $tokenId])
            ->assertExitCode(0)
            ->expectsOutputToContain('has been revoked');

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    });

    it('exits 1 with an error for an unknown token ID', function (): void {
        $this->artisan('api:token:revoke', ['tokenId' => 99999])
            ->assertExitCode(1)
            ->expectsOutputToContain('No token found');
    });
});
