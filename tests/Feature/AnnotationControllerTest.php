<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\AnnotationAssignment;
use App\Models\SubProject;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('AnnotationController', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('redirects guests to the login page', function (): void {
        $this->get(route('annotation.show', ['subProject' => 1]))
            ->assertRedirect('/login');
    });

    it('returns can_navigate=false and can_submit_all_pending=false for a strict, auto-submitting subproject', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $subProject = SubProject::factory()->create(['flexible' => false, 'auto_submission' => true]);
        $assignment = AnnotationAssignment::factory()->create([
            'user_id' => $user->id,
            'sub_project_id' => $subProject->id,
        ]);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('annotation.show', ['subProject' => $subProject->id]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('annotation/show')
                ->where('subProjectId', $subProject->id)
                ->where('can_navigate', false)
                ->where('can_submit_all_pending', false));
    });

    it('returns can_navigate=true and can_submit_all_pending=true for a flexible, non-auto subproject', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $subProject = SubProject::factory()->create(['flexible' => true, 'auto_submission' => false]);
        $assignment = AnnotationAssignment::factory()->create([
            'user_id' => $user->id,
            'sub_project_id' => $subProject->id,
        ]);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('annotation.show', ['subProject' => $subProject->id]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('annotation/show')
                ->where('can_navigate', true)
                ->where('can_submit_all_pending', true));
    });
});
