<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Enums\SubProjectPriorityEnum;
use App\Models\AnnotationAssignment;
use App\Models\AnnotationTask;
use App\Models\Dataset;
use App\Models\DatasetInstance;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;

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
