<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\AnnotationAssignment;
use App\Models\AnnotationTask;
use App\Models\AnnotatorOfProject;
use App\Models\Dataset;
use App\Models\DatasetInstance;
use App\Models\InstanceShuffleMapper;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\SubProject;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('ProjectController::store', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create()->assignRole(RolesEnum::ADMIN)->load('roles');
        $this->manager = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER)->load('roles');
        $this->annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR)->load('roles');

        $this->annotationTask = AnnotationTask::query()->create([
            'title' => 'Test Task',
            'short_description' => 'A test task',
            'weight' => 1,
        ]);

        $this->dataset = Dataset::query()->create([
            'name' => 'Test Dataset',
            'description' => 'A test dataset',
            'is_available' => true,
        ]);
    });

    it('creates a project and returns the correct DB state', function (): void {
        // Arrange
        $extraAnnotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR)->load('roles');

        $payload = [
            'name' => 'My New Project',
            'annotation_task_id' => $this->annotationTask->id,
            'dataset_id' => $this->dataset->id,
            'is_instance_shuffled' => false,
            'restricted_visibility' => false,
            'annotator_ids' => [$this->annotator->id, $extraAnnotator->id],
        ];

        // Act
        $response = $this->actingAs($this->admin)->post(route('projects.store'), $payload);

        // Assert
        $response->assertRedirect(route('projects.create'));

        $project = Project::query()->where('name', 'My New Project')->firstOrFail();

        expect($project->annotation_task_id)->toBe($this->annotationTask->id)
            ->and($project->dataset_id)->toBe($this->dataset->id)
            ->and($project->owner_user_id)->toBe($this->admin->id)
            ->and($project->is_instance_shuffled)->toBeFalse()
            ->and($project->restricted_visibility)->toBeFalse()
            ->and($project->annotation_task_configuration)->toBeNull();

        expect(ProjectManager::query()
            ->where('project_id', $project->id)
            ->where('user_id', $this->admin->id)
            ->exists()
        )->toBeTrue();

        expect(AnnotatorOfProject::query()
            ->where('project_id', $project->id)
            ->pluck('user_id')
            ->sort()
            ->values()
            ->all()
        )->toBe(collect([$this->annotator->id, $extraAnnotator->id])->sort()->values()->all());
    });

    it('stores annotation_task_configuration answers', function (): void {
        // Arrange
        $payload = [
            'name' => 'Configured Project',
            'annotation_task_id' => $this->annotationTask->id,
            'dataset_id' => $this->dataset->id,
            'is_instance_shuffled' => false,
            'restricted_visibility' => false,
            'annotator_ids' => [$this->annotator->id],
            'annotation_task_configuration' => [
                ['id' => 0, 'answer' => 'Yes'],
                ['id' => 1, 'answer' => 'No'],
            ],
        ];

        // Act
        $this->actingAs($this->manager)->post(route('projects.store'), $payload);

        // Assert
        $project = Project::query()->where('name', 'Configured Project')->firstOrFail();

        expect($project->annotation_task_configuration)->toBe([
            ['id' => 0, 'answer' => 'Yes'],
            ['id' => 1, 'answer' => 'No'],
        ]);
    });

    it('attaches co-managers', function (): void {
        // Arrange
        $coManager = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER)->load('roles');

        $payload = [
            'name' => 'Co-managed Project',
            'annotation_task_id' => $this->annotationTask->id,
            'dataset_id' => $this->dataset->id,
            'is_instance_shuffled' => false,
            'restricted_visibility' => false,
            'annotator_ids' => [$this->annotator->id],
            'co_manager_ids' => [$coManager->id],
        ];

        // Act
        $this->actingAs($this->manager)->post(route('projects.store'), $payload);

        // Assert
        $project = Project::query()->where('name', 'Co-managed Project')->firstOrFail();

        expect(ProjectManager::query()->where('project_id', $project->id)->count())->toBe(2)
            ->and(ProjectManager::query()
                ->where('project_id', $project->id)
                ->where('user_id', $coManager->id)
                ->exists()
            )->toBeTrue();
    });

    it('creates instance shuffle mappers when is_instance_shuffled is true', function (): void {
        // Arrange — seed 3 dataset instances so the shuffle mapper has something to index
        foreach (range(1, 3) as $i) {
            DatasetInstance::query()->create([
                'dataset_id' => $this->dataset->id,
                'index' => $i,
                'content' => 'instance ' . $i,
            ]);
        }

        $payload = [
            'name' => 'Shuffled Project',
            'annotation_task_id' => $this->annotationTask->id,
            'dataset_id' => $this->dataset->id,
            'is_instance_shuffled' => true,
            'restricted_visibility' => false,
            'annotator_ids' => [$this->annotator->id],
        ];

        // Act
        $this->actingAs($this->admin)->post(route('projects.store'), $payload);

        // Assert
        $project = Project::query()->where('name', 'Shuffled Project')->firstOrFail();

        expect($project->is_instance_shuffled)->toBeTrue();

        $mappers = InstanceShuffleMapper::query()
            ->where('project_id', $project->id)
            ->orderBy('new_index')
            ->get();

        // One mapper row per dataset instance
        expect($mappers)->toHaveCount(3);

        // new_index values must be 0, 1, 2 in order
        expect($mappers->pluck('new_index')->all())->toBe([1, 2, 3]);

        // old_index values must be a permutation of 0, 1, 2
        expect($mappers->pluck('old_index')->sort()->values()->all())->toBe([1, 2, 3]);
    });

    it('allows annotation managers to create projects', function (): void {
        // Arrange
        $payload = [
            'name' => 'Manager Project',
            'annotation_task_id' => $this->annotationTask->id,
            'dataset_id' => $this->dataset->id,
            'is_instance_shuffled' => false,
            'restricted_visibility' => false,
            'annotator_ids' => [$this->annotator->id],
        ];

        // Act
        $response = $this->actingAs($this->manager)->post(route('projects.store'), $payload);

        // Assert
        $response->assertRedirect(route('projects.create'));

        expect(Project::query()->where('name', 'Manager Project')->exists())->toBeTrue();
    });

    it('forbids annotators from creating projects', function (): void {
        // Arrange
        $payload = [
            'name' => 'Forbidden Project',
            'annotation_task_id' => $this->annotationTask->id,
            'dataset_id' => $this->dataset->id,
            'is_instance_shuffled' => false,
            'restricted_visibility' => false,
            'annotator_ids' => [$this->annotator->id],
        ];

        // Act & Assert
        $this->actingAs($this->annotator)
            ->post(route('projects.store'), $payload)
            ->assertForbidden();

        expect(Project::query()->where('name', 'Forbidden Project')->exists())->toBeFalse();
    });

    it('redirects guests to the login page', function (): void {
        $this->post(route('projects.store'), [])->assertRedirect(route('login'));
    });

    it('validates that name is required', function (): void {
        $payload = [
            'annotation_task_id' => $this->annotationTask->id,
            'dataset_id' => $this->dataset->id,
            'is_instance_shuffled' => false,
            'restricted_visibility' => false,
            'annotator_ids' => [$this->annotator->id],
        ];

        $this->actingAs($this->admin)
            ->post(route('projects.store'), $payload)
            ->assertSessionHasErrors('name');
    });

    it('validates that annotator_ids must have at least one entry', function (): void {
        $payload = [
            'name' => 'No Annotators Project',
            'annotation_task_id' => $this->annotationTask->id,
            'dataset_id' => $this->dataset->id,
            'is_instance_shuffled' => false,
            'restricted_visibility' => false,
            'annotator_ids' => [],
        ];

        $this->actingAs($this->admin)
            ->post(route('projects.store'), $payload)
            ->assertSessionHasErrors('annotator_ids');
    });

    it('validates that annotation_task_id must exist', function (): void {
        $payload = [
            'name' => 'Bad Task Project',
            'annotation_task_id' => 99999,
            'dataset_id' => $this->dataset->id,
            'is_instance_shuffled' => false,
            'restricted_visibility' => false,
            'annotator_ids' => [$this->annotator->id],
        ];

        $this->actingAs($this->admin)
            ->post(route('projects.store'), $payload)
            ->assertSessionHasErrors('annotation_task_id');
    });

    it('validates that dataset_id must exist', function (): void {
        $payload = [
            'name' => 'Bad Dataset Project',
            'annotation_task_id' => $this->annotationTask->id,
            'dataset_id' => 99999,
            'is_instance_shuffled' => false,
            'restricted_visibility' => false,
            'annotator_ids' => [$this->annotator->id],
        ];

        $this->actingAs($this->admin)
            ->post(route('projects.store'), $payload)
            ->assertSessionHasErrors('dataset_id');
    });
});

describe('ProjectController::changeStatus', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create()->assignRole(RolesEnum::ADMIN)->load('roles');
    });

    it('changes the status and returns ok with a success message', function (): void {
        // Arrange — a pending project may only transition to in_progress
        $project = Project::factory()->create(['status' => ProjectStatusEnum::PENDING]);

        // Act
        $response = $this->actingAs($this->admin)
            ->postJson(route('projects.change-status'), [
                'project_id' => $project->id,
                'status' => ProjectStatusEnum::IN_PROGRESS->value,
            ]);

        // Assert
        $response->assertOk();
        $response->assertJson(['success' => __('projects.messages.status_changed')]);

        expect($project->refresh()->status)->toBe(ProjectStatusEnum::IN_PROGRESS);
    });

    it('returns a 422 error and leaves the status unchanged on an invalid transition', function (): void {
        // Arrange — pending cannot jump straight to completed
        $project = Project::factory()->create(['status' => ProjectStatusEnum::PENDING]);

        // Act
        $response = $this->actingAs($this->admin)
            ->postJson(route('projects.change-status'), [
                'project_id' => $project->id,
                'status' => ProjectStatusEnum::COMPLETED->value,
            ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);

        expect($project->refresh()->status)->toBe(ProjectStatusEnum::PENDING);
    });
});

describe('ProjectController::toggleCanFlagOfAnnotator', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create()->assignRole(RolesEnum::ADMIN)->load('roles');
        $this->annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR)->load('roles');
    });

    it('toggles the can_flag pivot and returns ok with a success message', function (): void {
        // Arrange
        $project = Project::factory()->create();
        AnnotatorOfProject::factory()->create([
            'project_id' => $project->id,
            'user_id' => $this->annotator->id,
            'can_flag' => false,
        ]);

        // Act
        $response = $this->actingAs($this->admin)
            ->postJson(route('projects.toggle-can-flag'), [
                'project_id' => $project->id,
                'annotator_id' => $this->annotator->id,
            ]);

        // Assert
        $response->assertOk();
        $response->assertJson(['success' => __('projects.messages.can_flag_toggled')]);
        $this->assertDatabaseHas('annotator_of_project', [
            'project_id' => $project->id,
            'user_id' => $this->annotator->id,
            'can_flag' => true,
        ]);
    });
});

describe('ProjectController::detachAnnotator', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create()->assignRole(RolesEnum::ADMIN)->load('roles');
        $this->annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR)->load('roles');
    });

    it('detaches an annotator and returns ok with a success message', function (): void {
        // Arrange
        $project = Project::factory()->create(['status' => ProjectStatusEnum::PENDING]);
        AnnotatorOfProject::factory()->create([
            'project_id' => $project->id,
            'user_id' => $this->annotator->id,
        ]);

        // Act
        $response = $this->actingAs($this->admin)
            ->deleteJson(route('projects.annotators.detach', [$project->id, $this->annotator->id]));

        // Assert
        $response->assertOk();
        $response->assertJson(['success' => __('projects.messages.annotator_detached')]);
        $this->assertDatabaseMissing('annotator_of_project', [
            'project_id' => $project->id,
            'user_id' => $this->annotator->id,
        ]);
    });

    it('returns a 422 error when the annotator has subproject assignments', function (): void {
        // Arrange — an assignment in one of the project's subprojects blocks detachment
        $project = Project::factory()->create(['status' => ProjectStatusEnum::PENDING]);
        AnnotatorOfProject::factory()->create([
            'project_id' => $project->id,
            'user_id' => $this->annotator->id,
        ]);
        $subProject = SubProject::factory()->create(['project_id' => $project->id]);
        AnnotationAssignment::factory()->create([
            'user_id' => $this->annotator->id,
            'sub_project_id' => $subProject->id,
        ]);

        // Act
        $response = $this->actingAs($this->admin)
            ->deleteJson(route('projects.annotators.detach', [$project->id, $this->annotator->id]));

        // Assert — PresentableError surfaces as JSON, the link survives
        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseHas('annotator_of_project', [
            'project_id' => $project->id,
            'user_id' => $this->annotator->id,
        ]);
    });
});

describe('ProjectController::destroy', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create()->assignRole(RolesEnum::ADMIN)->load('roles');
        $this->annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR)->load('roles');
    });

    it('deletes a project and returns ok with a success message', function (): void {
        // Arrange
        $project = Project::factory()->create(['status' => ProjectStatusEnum::PENDING]);

        // Act
        $response = $this->actingAs($this->admin)
            ->deleteJson(route('projects.destroy', $project->id));

        // Assert
        $response->assertOk();
        $response->assertJson(['success' => __('projects.messages.deleted')]);
        $this->assertSoftDeleted($project);
    });

    it('forbids an annotator from deleting a project', function (): void {
        // Arrange
        $project = Project::factory()->create(['status' => ProjectStatusEnum::PENDING]);

        // Act / Assert
        $this->actingAs($this->annotator)
            ->deleteJson(route('projects.destroy', $project->id))
            ->assertForbidden();

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'deleted_at' => null]);
    });
});
