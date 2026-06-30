<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\AnnotationAssignment;
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

    it('renders the annotation page with the subproject id and requested mode', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $assignment = AnnotationAssignment::factory()->create(['user_id' => $user->id]);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('annotation.show', [
                'subProject' => $assignment->sub_project_id,
                'mode' => 'flexible',
                'annotation_assignment_id' => $assignment->id,
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('annotation/show')
                ->where('subProjectId', $assignment->sub_project_id)
                ->where('mode', 'flexible'));
    });

    it('defaults to strict mode when no mode is provided', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $assignment = AnnotationAssignment::factory()->create(['user_id' => $user->id]);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('annotation.show', [
                'subProject' => $assignment->sub_project_id,
                'annotation_assignment_id' => $assignment->id,
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('mode', 'strict'));
    });

    it('falls back to strict mode when an invalid mode is provided', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $assignment = AnnotationAssignment::factory()->create(['user_id' => $user->id]);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('annotation.show', [
                'subProject' => $assignment->sub_project_id,
                'mode' => 'bogus',
                'annotation_assignment_id' => $assignment->id,
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('mode', 'strict'));
    });
});
