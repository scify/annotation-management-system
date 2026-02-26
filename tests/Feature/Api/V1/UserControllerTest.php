<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('UserController', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('rejects unauthenticated requests', function (): void {
        // Act
        $response = $this->getJson('/api/v1/user/info');

        // Assert
        $response->assertUnauthorized()
            ->assertJson(['error' => 'Unauthenticated.']);
    });

    it('returns user info and empty permissions for a user with no role', function (): void {
        // Arrange
        $user = User::factory()->create(['name' => 'Test User']);

        // Act
        $response = $this->actingAs($user)->getJson('/api/v1/user/info');

        // Assert
        $response->assertSuccessful()
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => 'Test User',
                    'email' => $user->email,
                    'role' => null,
                ],
            ])
            ->assertJsonPath('permissions', []);
    });

    it('grants dashboard permission to admins', function (): void {
        // Arrange
        $user = User::factory()->create(['name' => 'Admin User'])
            ->assignRole(RolesEnum::ADMINISTRATOR->value);

        // Act
        $response = $this->actingAs($user)->getJson('/api/v1/user/info');

        // Assert
        $response->assertSuccessful()
            ->assertJson([
                'user' => [
                    'name' => 'Admin User',
                    'role' => RolesEnum::ADMINISTRATOR->value,
                ],
                'permissions' => ['dashboard' => true],
            ]);
    });

    it('grants dashboard permission to user managers', function (): void {
        // Arrange
        $user = User::factory()->create(['name' => 'Manager User'])
            ->assignRole(RolesEnum::USER_MANAGER->value);

        // Act
        $response = $this->actingAs($user)->getJson('/api/v1/user/info');

        // Assert
        $response->assertSuccessful()
            ->assertJson([
                'user' => [
                    'name' => 'Manager User',
                    'role' => RolesEnum::USER_MANAGER->value,
                ],
                'permissions' => ['dashboard' => true],
            ]);
    });

    it('denies dashboard permission to regular users', function (): void {
        // Arrange
        $user = User::factory()->create(['name' => 'Regular User'])
            ->assignRole(RolesEnum::REGISTERED_USER->value);

        // Act
        $response = $this->actingAs($user)->getJson('/api/v1/user/info');

        // Assert
        $response->assertSuccessful()
            ->assertJson([
                'user' => [
                    'name' => 'Regular User',
                    'role' => RolesEnum::REGISTERED_USER->value,
                ],
            ])
            ->assertJsonPath('permissions', []);
    });
});
