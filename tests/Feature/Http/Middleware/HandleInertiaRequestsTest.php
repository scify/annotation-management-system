<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\ThreadMember;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('HandleInertiaRequests shared auth.user.new_notifications_exist', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('is true when the user has an unread notification', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ADMIN->value);
        ThreadMember::factory()->create(['user_id' => $user->id, 'is_read' => false]);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('auth.user.new_notifications_exist', true)
            );
    });

    it('is false when all the user notifications are read', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ADMIN->value);
        ThreadMember::factory()->create(['user_id' => $user->id, 'is_read' => true]);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('auth.user.new_notifications_exist', false)
            );
    });

    it("ignores other users' unread notifications", function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ADMIN->value);
        $other = User::factory()->create();
        ThreadMember::factory()->create(['user_id' => $other->id, 'is_read' => false]);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('auth.user.new_notifications_exist', false)
            );
    });
});
