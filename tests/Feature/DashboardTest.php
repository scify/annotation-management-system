<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\AnnotationAssignment;
use App\Models\SubProject;
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

    it('renders the annotator dashboard with subprojects for annotators', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);

        // Act & Assert
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('dashboard-annotator')
                ->has('subprojects'));
    });

    it('lists in-progress subprojects (but not other statuses) on the annotator dashboard', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $activeSubProject = SubProject::factory()->create(['status' => ProjectStatusEnum::IN_PROGRESS, 'name' => 'Active batch']);
        SubProject::factory()->create(['status' => ProjectStatusEnum::COMPLETED, 'name' => 'Done batch']);
        AnnotationAssignment::factory()->create(['user_id' => $user->id, 'sub_project_id' => $activeSubProject->id]);

        // Act & Assert
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('dashboard-annotator')
                ->has('subprojects', 1)
                ->where('subprojects.0.name', 'Active batch'));
    });
});
