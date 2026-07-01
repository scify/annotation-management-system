<?php

declare(strict_types=1);

use App\Enums\AnnotationTaskTypeEnum;
use App\Enums\ConfidenceEnum;
use App\Enums\RolesEnum;
use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\AnnotationTask;
use App\Models\Project;
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

    it('persists the annotation and redirects to the show page on submit', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $task = AnnotationTask::factory()->create([
            'task_type' => AnnotationTaskTypeEnum::LEXICAL_SEMANTIC_CHANGE_DETECTION,
        ]);
        $project = Project::factory()->create(['annotation_task_id' => $task->id]);
        $subProject = SubProject::factory()->create([
            'project_id' => $project->id,
            'flexible' => true,
            'auto_submission' => false,
        ]);
        $assignment = AnnotationAssignment::factory()->create([
            'user_id' => $user->id,
            'sub_project_id' => $subProject->id,
        ]);
        $annotation = Annotation::factory()->create([
            'annotation_assignment_id' => $assignment->id,
            'annotations' => null,
        ]);

        // Act
        $response = $this->actingAs($user)->post(
            route('annotation.submit-annotation', ['subProject' => $subProject->id]),
            [
                'annotation_id' => $annotation->id,
                'annotation_session_id' => 1,
                'annotations' => [
                    ['key' => 'yes', 'is_selected' => true],
                    ['key' => 'no', 'is_selected' => false],
                ],
                'pending' => true,
                'confidence' => ConfidenceEnum::HIGH->value,
                'active_filter' => 'all',
            ],
        );

        // Assert
        $response->assertRedirect(route('annotation.show', ['subProject' => $subProject->id, 'active_filter' => 'all', 'annotation_session_id' => 1]));
        $response->assertSessionHas('success', __('annotation.submit_success'));

        $annotation->refresh();
        expect($annotation->annotations)->toBe([
            ['key' => 'yes', 'is_selected' => true],
            ['key' => 'no', 'is_selected' => false],
        ]);
        expect($annotation->pending)->toBeTrue();
        expect($annotation->confidence)->toBe(ConfidenceEnum::HIGH);
    });

    it('redirects to the show page on previous navigation', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $subProject = SubProject::factory()->create();

        // Act
        $response = $this->actingAs($user)->post(
            route('annotation.previous', ['subProject' => $subProject->id]),
            ['active_filter' => 'pending'],
        );

        // Assert
        $response->assertRedirect(route('annotation.show', ['subProject' => $subProject->id, 'active_filter' => 'pending']));
    });

    it('redirects to the show page on next navigation', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $subProject = SubProject::factory()->create();

        // Act
        $response = $this->actingAs($user)->post(
            route('annotation.next', ['subProject' => $subProject->id]),
        );

        // Assert — no filter posted, so it defaults to 'all'
        $response->assertRedirect(route('annotation.show', ['subProject' => $subProject->id, 'active_filter' => 'all']));
    });

    it('renders the show page for a given active filter', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $subProject = SubProject::factory()->create(['flexible' => true, 'auto_submission' => false]);
        AnnotationAssignment::factory()->create([
            'user_id' => $user->id,
            'sub_project_id' => $subProject->id,
        ]);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('annotation.show', ['subProject' => $subProject->id, 'active_filter' => 'all']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('annotation/show')
                ->where('subProjectId', $subProject->id));
    });
});
