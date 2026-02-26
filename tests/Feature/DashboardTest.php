<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('DashboardController', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('redirects guests to the login page', function (): void {
        $this->get('/dashboard')->assertRedirect('/login');
    });

    it('renders the full dashboard for admins', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ADMIN->value);

        // Act & Assert
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('dashboard'));
    });

    it('renders the full dashboard for annotation managers', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER->value);

        // Act & Assert
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('dashboard'));
    });

    it('renders the simple dashboard for annotators', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);

        // Act & Assert
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('dashboard-simple'));
    });
});
