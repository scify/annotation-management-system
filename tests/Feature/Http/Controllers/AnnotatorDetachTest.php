<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\AnnotatorOfProject;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use App\Services\SubProject\SubProjectReadService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ProjectController - detach annotator', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create()->assignRole(RolesEnum::ADMIN->value);
        $this->annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $this->project = Project::factory()->create();

        AnnotatorOfProject::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->annotator->id,
        ]);
    });

    it('detaches an annotator with no subproject assignments from a project', function (): void {
        // Arrange
        $this->actingAs($this->admin)->get(route('users.index'));

        // Act
        $response = $this->deleteJson(
            route('projects.annotators.detach', [$this->project->id, $this->annotator->id]),
        );

        // Assert
        $response->assertOk();
        $response->assertJson(['success' => __('projects.messages.annotator_detached')]);

        $this->assertDatabaseMissing('annotator_of_project', [
            'project_id' => $this->project->id,
            'user_id' => $this->annotator->id,
        ]);
    });

    it('blocks detaching an annotator who has subproject assignments', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create(['project_id' => $this->project->id]);
        AnnotationAssignment::factory()->create([
            'sub_project_id' => $subProject->id,
            'user_id' => $this->annotator->id,
        ]);

        $this->actingAs($this->admin)->get(route('users.index'));

        // Act
        $response = $this->deleteJson(
            route('projects.annotators.detach', [$this->project->id, $this->annotator->id]),
        );

        // Assert
        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);

        $this->assertDatabaseHas('annotator_of_project', [
            'project_id' => $this->project->id,
            'user_id' => $this->annotator->id,
        ]);
    });

    it('returns 403 for annotators trying to detach', function (): void {
        // Arrange
        $this->actingAs($this->annotator)->get(route('users.index'));

        // Act
        $response = $this->deleteJson(
            route('projects.annotators.detach', [$this->project->id, $this->annotator->id]),
        );

        // Assert
        $response->assertForbidden();
    });

    it('returns 422 when the annotator is not in the project', function (): void {
        // Arrange
        $outsider = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $this->actingAs($this->admin)->get(route('users.index'));

        // Act
        $response = $this->deleteJson(
            route('projects.annotators.detach', [$this->project->id, $outsider->id]),
        );

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('annotator_id');
    });
});

describe('SubProjectController - detach annotator', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create()->assignRole(RolesEnum::ADMIN->value);
        $this->annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $this->project = Project::factory()->create();

        AnnotatorOfProject::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->annotator->id,
        ]);

        $this->subProject = SubProject::factory()->create([
            'project_id' => $this->project->id,
            'status' => ProjectStatusEnum::PENDING,
        ]);

        $this->assignment = AnnotationAssignment::factory()->create([
            'sub_project_id' => $this->subProject->id,
            'user_id' => $this->annotator->id,
        ]);
    });

    it('detaches an annotator from a pending subproject and removes their annotations', function (): void {
        // Arrange
        $annotation = Annotation::factory()->create([
            'annotation_assignment_id' => $this->assignment->id,
        ]);

        $this->actingAs($this->admin)->get(route('users.index'));

        // Act
        $response = $this->deleteJson(
            route('projects.subprojects.annotators.detach', [$this->project->id, $this->subProject->id, $this->annotator->id]),
        );

        // Assert
        $response->assertOk();
        $response->assertJson(['success' => __('sub-projects.messages.annotator_detached')]);

        $this->assertDatabaseMissing('annotation_assignments', ['id' => $this->assignment->id]);
        $this->assertDatabaseMissing('annotations', ['id' => $annotation->id]);
    });

    it('blocks detaching an annotator from a non-pending subproject', function (): void {
        // Arrange
        $this->subProject->update(['status' => ProjectStatusEnum::IN_PROGRESS]);
        $this->actingAs($this->admin)->get(route('users.index'));

        // Act
        $response = $this->deleteJson(
            route('projects.subprojects.annotators.detach', [$this->project->id, $this->subProject->id, $this->annotator->id]),
        );

        // Assert
        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);

        $this->assertDatabaseHas('annotation_assignments', ['id' => $this->assignment->id]);
    });

    it('returns 403 for annotators trying to detach from a subproject', function (): void {
        // Arrange
        $this->actingAs($this->annotator)->get(route('users.index'));

        // Act
        $response = $this->deleteJson(
            route('projects.subprojects.annotators.detach', [$this->project->id, $this->subProject->id, $this->annotator->id]),
        );

        // Assert
        $response->assertForbidden();
    });

    it('returns 422 when the annotator is not in the subproject', function (): void {
        // Arrange
        $outsider = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $this->actingAs($this->admin)->get(route('users.index'));

        // Act
        $response = $this->deleteJson(
            route('projects.subprojects.annotators.detach', [$this->project->id, $this->subProject->id, $outsider->id]),
        );

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('annotator_id');
    });

    it('includes can_be_removed=true in annotators data when subproject is pending', function (): void {
        // Arrange — subProject is already PENDING from beforeEach
        $service = resolve(SubProjectReadService::class);

        // Act
        $data = $service->getDataForEditSubProject($this->project->id, $this->subProject->id);

        // Assert
        expect($data['annotators_data'])->toHaveCount(1)
            ->and($data['annotators_data'][0]['can_be_removed'])->toBeTrue();
    });

    it('includes can_be_removed=false in annotators data when subproject is not pending', function (): void {
        // Arrange
        $this->subProject->update(['status' => ProjectStatusEnum::IN_PROGRESS]);
        $service = resolve(SubProjectReadService::class);

        // Act
        $data = $service->getDataForEditSubProject($this->project->id, $this->subProject->id);

        // Assert
        expect($data['annotators_data'])->toHaveCount(1)
            ->and($data['annotators_data'][0]['can_be_removed'])->toBeFalse();
    });
});
