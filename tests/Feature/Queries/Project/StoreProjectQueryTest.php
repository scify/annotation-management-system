<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Models\AnnotationTask;
use App\Models\Dataset;
use App\Models\Project;
use App\Models\User;
use App\Queries\Project\StoreProjectQuery;

describe('StoreProjectQuery', function (): void {
    it('creates a pending project owned by the given user', function (): void {
        // Arrange
        $owner = User::factory()->create();
        $task = AnnotationTask::factory()->create();
        $dataset = Dataset::factory()->create();
        $data = [
            'name' => 'Sentiment Pass 1',
            'annotation_task_id' => $task->id,
            'dataset_id' => $dataset->id,
            'is_instance_shuffled' => true,
            'restricted_visibility' => false,
        ];

        // Act
        $project = new StoreProjectQuery()->execute($data, $owner->id);

        // Assert
        expect($project)->toBeInstanceOf(Project::class)
            ->and($project->status)->toBe(ProjectStatusEnum::PENDING)
            ->and($project->owner_user_id)->toBe($owner->id);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Sentiment Pass 1',
            'status' => ProjectStatusEnum::PENDING->value,
            'is_instance_shuffled' => true,
        ]);
    });

    it('defaults optional fields to null when omitted', function (): void {
        // Arrange
        $owner = User::factory()->create();
        $task = AnnotationTask::factory()->create();
        $dataset = Dataset::factory()->create();
        $data = [
            'name' => 'No Optionals',
            'annotation_task_id' => $task->id,
            'dataset_id' => $dataset->id,
            'is_instance_shuffled' => false,
            'restricted_visibility' => true,
        ];

        // Act
        $project = new StoreProjectQuery()->execute($data, $owner->id);

        // Assert
        expect($project->annotation_task_configuration)->toBeNull()
            ->and($project->scheduled_at)->toBeNull()
            ->and($project->deadline_at)->toBeNull();
    });
});
