<?php

declare(strict_types=1);

use App\Enums\AnnotationTaskTypeEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Enums\SubProjectPriorityEnum;
use App\Models\AnnotationAssignment;
use App\Models\AnnotationTask;
use App\Models\Dataset;
use App\Models\DatasetInstance;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\SubProject;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;

/**
 * Builds a complete, render-ready project graph shared by the action tests below:
 * one user per role (the annotation manager is intentionally NOT a ProjectManager,
 * so it exercises the ProjectManager-gated 403 paths), a project in IN_PROGRESS with
 * a DUMMY annotation task, a dataset with 10 instances, and the annotator linked to
 * the project. Subprojects are created per-test in the status each scenario needs.
 */
function bootSubProjectFixtures(): object {
    $admin = User::factory()->create()->assignRole(RolesEnum::ADMIN)->load('roles');
    $manager = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER)->load('roles');
    $annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR)->load('roles');

    $task = AnnotationTask::factory()->create(['task_type' => AnnotationTaskTypeEnum::DUMMY]);

    $dataset = Dataset::query()->create([
        'name' => 'Test Dataset',
        'description' => 'A test dataset',
        'is_available' => true,
    ]);

    foreach (range(1, 10) as $i) {
        DatasetInstance::query()->create([
            'dataset_id' => $dataset->id,
            'index' => $i,
            'content' => 'instance ' . $i,
        ]);
    }

    $project = Project::query()->create([
        'name' => 'Test Project',
        'owner_user_id' => $admin->id,
        'annotation_task_id' => $task->id,
        'dataset_id' => $dataset->id,
        'status' => ProjectStatusEnum::IN_PROGRESS,
        'restricted_visibility' => false,
        'is_instance_shuffled' => false,
    ]);

    $project->annotators()->syncWithoutDetaching([$annotator->id]);

    return (object) ['admin' => $admin, 'manager' => $manager, 'annotator' => $annotator, 'project' => $project, 'dataset' => $dataset, 'task' => $task];
}

describe('SubProjectController::store — shuffle', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create()->assignRole(RolesEnum::ADMIN)->load('roles');
        $this->annotatorA = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR)->load('roles');
        $this->annotatorB = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR)->load('roles');

        $annotationTask = AnnotationTask::query()->create([
            'title' => 'Test Task',
            'short_description' => 'A test task',
            'weight' => 1,
        ]);

        $dataset = Dataset::query()->create([
            'name' => 'Test Dataset',
            'description' => 'A test dataset',
            'is_available' => true,
        ]);

        foreach (range(1, 10) as $i) {
            DatasetInstance::query()->create([
                'dataset_id' => $dataset->id,
                'index' => $i,
                'content' => 'instance ' . $i,
            ]);
        }

        $this->project = Project::query()->create([
            'name' => 'Test Project',
            'owner_user_id' => $this->admin->id,
            'annotation_task_id' => $annotationTask->id,
            'dataset_id' => $dataset->id,
            'status' => ProjectStatusEnum::IN_PROGRESS,
            'restricted_visibility' => false,
            'is_instance_shuffled' => false,
        ]);

        $this->dataset = $dataset;

        $this->project->annotators()->syncWithoutDetaching([
            $this->annotatorA->id,
            $this->annotatorB->id,
        ]);
    });

    it('creates annotation rows where annotator_instance_index equals project_instance_index when shuffle is false', function (): void {
        // Arrange
        $payload = [
            'name' => 'Batch 1',
            'annotator_ids' => [$this->annotatorA->id, $this->annotatorB->id],
            'shuffle' => false,
            'from_instance' => 1,
            'to_instance' => 10,
            'dataset_id' => $this->dataset->id,
            'priority' => SubProjectPriorityEnum::MEDIUM->value,
            'is_flexible' => false,
        ];

        // Act
        $this->actingAs($this->admin)
            ->post(route('projects.subprojects.store', $this->project->id), $payload)
            ->assertRedirect();

        // Assert — every row has matching indexes
        $rows = DB::table('annotations')->get();

        expect($rows)->not->toBeEmpty();

        foreach ($rows as $row) {
            expect($row->annotator_instance_index)->toBe($row->project_instance_index);
        }
    });

    it('creates annotation rows with differing per-annotator orderings when shuffle is true', function (): void {
        // Arrange
        $payload = [
            'name' => 'Batch 1',
            'annotator_ids' => [$this->annotatorA->id, $this->annotatorB->id],
            'shuffle' => true,
            'from_instance' => 1,
            'to_instance' => 10,
            'dataset_id' => $this->dataset->id,
            'priority' => SubProjectPriorityEnum::MEDIUM->value,
            'is_flexible' => false,
        ];

        // Act
        $this->actingAs($this->admin)
            ->post(route('projects.subprojects.store', $this->project->id), $payload)
            ->assertRedirect();

        // Assert
        $assignmentA = AnnotationAssignment::query()->where('user_id', $this->annotatorA->id)->firstOrFail();
        $assignmentB = AnnotationAssignment::query()->where('user_id', $this->annotatorB->id)->firstOrFail();

        // Retrieve project_instance_index values in each annotator's personal order
        $orderA = DB::table('annotations')
            ->where('annotation_assignment_id', $assignmentA->id)
            ->orderBy('annotator_instance_index')
            ->pluck('project_instance_index')
            ->all();

        $orderB = DB::table('annotations')
            ->where('annotation_assignment_id', $assignmentB->id)
            ->orderBy('annotator_instance_index')
            ->pluck('project_instance_index')
            ->all();

        // Each annotator's annotator_instance_index covers the full range (valid permutation)
        expect(collect($orderA)->sort()->values()->all())->toBe(range(1, 10));
        expect(collect($orderB)->sort()->values()->all())->toBe(range(1, 10));

        // The two annotators see instances in a different order
        // P(identical permutations) = 1/10! ≈ 0 — safe to assert inequality
        expect($orderA)->not->toBe($orderB);
    });
});

describe('SubProjectController::create', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->ctx = bootSubProjectFixtures();
    });

    it('renders the create page for admins and annotation managers', function (): void {
        // Arrange / Act / Assert
        $this->actingAs($this->ctx->admin)
            ->get(route('projects.subprojects.create', $this->ctx->project->id))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('sub-projects/create')
                ->has('subset_data'));

        $this->actingAs($this->ctx->manager)
            ->get(route('projects.subprojects.create', $this->ctx->project->id))
            ->assertOk();
    });

    it('forbids annotators from the create page', function (): void {
        // Arrange / Act / Assert
        $this->actingAs($this->ctx->annotator)
            ->get(route('projects.subprojects.create', $this->ctx->project->id))
            ->assertForbidden();
    });
});

describe('SubProjectController::changeStatus', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->ctx = bootSubProjectFixtures();
    });

    it('promotes a pending subproject (and its pending parent) to in_progress', function (): void {
        // Arrange — parent pending so the auto-promotion branch runs
        $this->ctx->project->update(['status' => ProjectStatusEnum::PENDING]);
        $subProject = SubProject::factory()->create([
            'project_id' => $this->ctx->project->id,
            'status' => ProjectStatusEnum::PENDING,
        ]);

        // Act
        $response = $this->actingAs($this->ctx->admin)
            ->post(route('sub-projects.change-status'), [
                'sub_project_id' => $subProject->id,
                'status' => ProjectStatusEnum::IN_PROGRESS->value,
            ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success');

        expect($subProject->fresh()->status)->toBe(ProjectStatusEnum::IN_PROGRESS);
        expect($this->ctx->project->fresh()->status)->toBe(ProjectStatusEnum::IN_PROGRESS);
    });

    it('flashes an error and leaves status unchanged on an invalid transition', function (): void {
        // Arrange — pending → completed is not an allowed single-step transition
        $subProject = SubProject::factory()->create([
            'project_id' => $this->ctx->project->id,
            'status' => ProjectStatusEnum::PENDING,
        ]);

        // Act
        $response = $this->actingAs($this->ctx->admin)
            ->post(route('sub-projects.change-status'), [
                'sub_project_id' => $subProject->id,
                'status' => ProjectStatusEnum::COMPLETED->value,
            ]);

        // Assert — catch block in the controller surfaces the PresentableError
        $response->assertSessionHas('error');

        expect($subProject->fresh()->status)->toBe(ProjectStatusEnum::PENDING);
    });

    it('flashes an error when completing a subproject whose parent is not in progress', function (): void {
        // Arrange
        $this->ctx->project->update(['status' => ProjectStatusEnum::PENDING]);
        $subProject = SubProject::factory()->create([
            'project_id' => $this->ctx->project->id,
            'status' => ProjectStatusEnum::IN_PROGRESS,
        ]);

        // Act
        $response = $this->actingAs($this->ctx->admin)
            ->post(route('sub-projects.change-status'), [
                'sub_project_id' => $subProject->id,
                'status' => ProjectStatusEnum::COMPLETED->value,
            ]);

        // Assert
        $response->assertSessionHas('error');

        expect($subProject->fresh()->status)->toBe(ProjectStatusEnum::IN_PROGRESS);
    });

    it('forbids an annotation manager who is not a project manager', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create([
            'project_id' => $this->ctx->project->id,
            'status' => ProjectStatusEnum::PENDING,
        ]);

        // Act / Assert
        $this->actingAs($this->ctx->manager)
            ->post(route('sub-projects.change-status'), [
                'sub_project_id' => $subProject->id,
                'status' => ProjectStatusEnum::IN_PROGRESS->value,
            ])
            ->assertForbidden();
    });

    it('validates the status against the enum', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create([
            'project_id' => $this->ctx->project->id,
            'status' => ProjectStatusEnum::PENDING,
        ]);

        // Act / Assert
        $this->actingAs($this->ctx->admin)
            ->post(route('sub-projects.change-status'), [
                'sub_project_id' => $subProject->id,
                'status' => 'not-a-real-status',
            ])
            ->assertSessionHasErrors('status');
    });
});

describe('SubProjectController::edit', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->ctx = bootSubProjectFixtures();
    });

    it('renders the edit page for admins', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create(['project_id' => $this->ctx->project->id]);

        // Act / Assert
        $this->actingAs($this->ctx->admin)
            ->get(route('projects.subprojects.edit', [$this->ctx->project->id, $subProject->id]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('sub-projects/edit')
                ->has('subproject_data'));
    });

    it('forbids annotators from the edit page', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create(['project_id' => $this->ctx->project->id]);

        // Act / Assert
        $this->actingAs($this->ctx->annotator)
            ->get(route('projects.subprojects.edit', [$this->ctx->project->id, $subProject->id]))
            ->assertForbidden();
    });
});

describe('SubProjectController::showAddAnnotators', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->ctx = bootSubProjectFixtures();
    });

    it('renders the add-annotators page for admins', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create(['project_id' => $this->ctx->project->id]);

        // Act / Assert
        $this->actingAs($this->ctx->admin)
            ->get(route('projects.subprojects.annotators.add', [$this->ctx->project->id, $subProject->id]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('sub-projects/add-annotators')
                ->has('annotators_data'));
    });

    it('forbids annotators from the add-annotators page', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create(['project_id' => $this->ctx->project->id]);

        // Act / Assert
        $this->actingAs($this->ctx->annotator)
            ->get(route('projects.subprojects.annotators.add', [$this->ctx->project->id, $subProject->id]))
            ->assertForbidden();
    });
});

describe('SubProjectController::attachAnnotators', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->ctx = bootSubProjectFixtures();
    });

    it('attaches annotators and flashes success', function (): void {
        // Arrange — bound the instance range to the dataset's 10 instances
        $subProject = SubProject::factory()->create([
            'project_id' => $this->ctx->project->id,
            'status' => ProjectStatusEnum::PENDING,
            'first_instance_index' => 1,
            'last_instance_index' => 10,
        ]);

        // Act
        $response = $this->actingAs($this->ctx->admin)
            ->post(route('projects.subprojects.annotators.attach', [$this->ctx->project->id, $subProject->id]), [
                'annotator_ids' => [$this->ctx->annotator->id],
            ]);

        // Assert
        $response->assertRedirect(route('projects.subprojects.edit', [$this->ctx->project->id, $subProject->id]));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('annotation_assignments', [
            'user_id' => $this->ctx->annotator->id,
            'sub_project_id' => $subProject->id,
        ]);
    });

    it('forbids an annotation manager who is not a project manager', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create(['project_id' => $this->ctx->project->id]);

        // Act / Assert
        $this->actingAs($this->ctx->manager)
            ->post(route('projects.subprojects.annotators.attach', [$this->ctx->project->id, $subProject->id]), [
                'annotator_ids' => [$this->ctx->annotator->id],
            ])
            ->assertForbidden();
    });

    it('requires at least one annotator id', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create(['project_id' => $this->ctx->project->id]);

        // Act / Assert
        $this->actingAs($this->ctx->admin)
            ->post(route('projects.subprojects.annotators.attach', [$this->ctx->project->id, $subProject->id]), [
                'annotator_ids' => [],
            ])
            ->assertSessionHasErrors('annotator_ids');
    });
});

describe('SubProjectController::detachAnnotator', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->ctx = bootSubProjectFixtures();
    });

    it('detaches an annotator from a pending subproject and flashes success', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create([
            'project_id' => $this->ctx->project->id,
            'status' => ProjectStatusEnum::PENDING,
        ]);
        AnnotationAssignment::factory()->create([
            'user_id' => $this->ctx->annotator->id,
            'sub_project_id' => $subProject->id,
        ]);

        // Act
        $response = $this->actingAs($this->ctx->admin)
            ->delete(route('projects.subprojects.annotators.detach', [
                $this->ctx->project->id, $subProject->id, $this->ctx->annotator->id,
            ]));

        // Assert
        $response->assertRedirect(route('projects.subprojects.edit', [$this->ctx->project->id, $subProject->id]));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('annotation_assignments', [
            'user_id' => $this->ctx->annotator->id,
            'sub_project_id' => $subProject->id,
        ]);
    });

    it('flashes an error when the subproject is not pending', function (): void {
        // Arrange — in_progress subproject blocks detachment
        $subProject = SubProject::factory()->create([
            'project_id' => $this->ctx->project->id,
            'status' => ProjectStatusEnum::IN_PROGRESS,
        ]);
        AnnotationAssignment::factory()->create([
            'user_id' => $this->ctx->annotator->id,
            'sub_project_id' => $subProject->id,
        ]);

        // Act
        $response = $this->actingAs($this->ctx->admin)
            ->delete(route('projects.subprojects.annotators.detach', [
                $this->ctx->project->id, $subProject->id, $this->ctx->annotator->id,
            ]));

        // Assert — catch block surfaces the PresentableError, assignment survives
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('annotation_assignments', [
            'user_id' => $this->ctx->annotator->id,
            'sub_project_id' => $subProject->id,
        ]);
    });

    it('forbids annotators from detaching', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create([
            'project_id' => $this->ctx->project->id,
            'status' => ProjectStatusEnum::PENDING,
        ]);
        AnnotationAssignment::factory()->create([
            'user_id' => $this->ctx->annotator->id,
            'sub_project_id' => $subProject->id,
        ]);

        // Act / Assert
        $this->actingAs($this->ctx->annotator)
            ->delete(route('projects.subprojects.annotators.detach', [
                $this->ctx->project->id, $subProject->id, $this->ctx->annotator->id,
            ]))
            ->assertForbidden();
    });
});

describe('SubProjectController::destroy', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->ctx = bootSubProjectFixtures();
    });

    it('deletes a pending subproject and redirects to the project page', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create([
            'project_id' => $this->ctx->project->id,
            'status' => ProjectStatusEnum::PENDING,
        ]);

        // Act
        $response = $this->actingAs($this->ctx->admin)
            ->delete(route('projects.subprojects.destroy', [$this->ctx->project->id, $subProject->id]));

        // Assert
        $response->assertRedirect(route('projects.show', $this->ctx->project->id));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('sub_projects', ['id' => $subProject->id]);
    });

    it('forbids a project manager from deleting a subproject that is not pending', function (): void {
        // Arrange — a non-admin project manager bypasses the role check but is still
        // blocked by the PENDING guard (admins would bypass it via Gate::before).
        ProjectManager::factory()->create([
            'project_id' => $this->ctx->project->id,
            'user_id' => $this->ctx->manager->id,
        ]);
        $subProject = SubProject::factory()->create([
            'project_id' => $this->ctx->project->id,
            'status' => ProjectStatusEnum::IN_PROGRESS,
        ]);

        // Act / Assert — deleteSubProject policy denies deletion of non-pending subprojects
        $this->actingAs($this->ctx->manager)
            ->delete(route('projects.subprojects.destroy', [$this->ctx->project->id, $subProject->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('sub_projects', ['id' => $subProject->id]);
    });

    it('forbids annotators from deleting a subproject', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create([
            'project_id' => $this->ctx->project->id,
            'status' => ProjectStatusEnum::PENDING,
        ]);

        // Act / Assert
        $this->actingAs($this->ctx->annotator)
            ->delete(route('projects.subprojects.destroy', [$this->ctx->project->id, $subProject->id]))
            ->assertForbidden();
    });
});

describe('SubProjectController::update', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->ctx = bootSubProjectFixtures();
    });

    // NOTE: SubProjectWriteService::updateSubProject is an unimplemented TODO (no-op),
    // so these tests assert only authorization + redirect/flash, not persisted changes.
    it('authorizes admins and redirects with success', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create(['project_id' => $this->ctx->project->id]);

        // Act
        $response = $this->actingAs($this->ctx->admin)
            ->put(route('projects.subprojects.update', [$this->ctx->project->id, $subProject->id]), [
                'name' => 'Updated name',
                'priority' => SubProjectPriorityEnum::MEDIUM->value,
                'is_flexible' => false,
                'minimum_annotations' => 1,
                'from_instance' => 1,
                'to_instance' => 10,
                'is_instance_shuffled_per_annotator' => false,
            ]);

        // Assert
        $response->assertRedirect(route('projects.subprojects.edit', [$this->ctx->project->id, $subProject->id]));
        $response->assertSessionHas('success');
    });

    it('forbids annotators from updating a subproject', function (): void {
        // Arrange
        $subProject = SubProject::factory()->create(['project_id' => $this->ctx->project->id]);

        // Act / Assert
        $this->actingAs($this->ctx->annotator)
            ->put(route('projects.subprojects.update', [$this->ctx->project->id, $subProject->id]), [
                'name' => 'Updated name',
                'priority' => SubProjectPriorityEnum::MEDIUM->value,
                'is_flexible' => false,
                'minimum_annotations' => 1,
                'from_instance' => 1,
                'to_instance' => 10,
                'is_instance_shuffled_per_annotator' => false,
            ])
            ->assertForbidden();
    });
});
