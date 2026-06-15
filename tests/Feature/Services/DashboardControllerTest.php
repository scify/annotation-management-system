<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('DashboardController::index', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('redirects unauthenticated users to the login page', function (): void {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    });

    it('renders the annotator dashboard with in-progress subprojects for annotators', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('dashboard-annotator')
                ->has('subprojects')
            );
    });

    it('passes all in-progress projects for admins via getAllInProgressProjects', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ADMIN->value);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('dashboard')
                ->has('my_projects')
            );
    });

    it('passes user-scoped in-progress projects for annotation managers via getMyInProgressProjects', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER->value);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('dashboard')
                ->has('my_projects')
            );
    });
});
