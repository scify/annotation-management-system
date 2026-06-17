<?php

declare(strict_types=1);

use App\Models\AnnotationTask;
use App\Models\Dataset;
use App\Models\DatasetInstance;
use App\Models\TaskTag;
use App\Models\User;
use App\Queries\Project\GetAnnotationTasksQuery;

describe('GetAnnotationTasksQuery', function (): void {
    it('returns all tasks with their tags and available datasets including instance counts', function (): void {
        // Arrange
        $task = AnnotationTask::factory()->create();
        $task->tags()->attach(TaskTag::factory()->create(['name' => 'NER'])->id);

        $available = Dataset::factory()->create(['is_available' => true]);
        DatasetInstance::factory()->count(2)->create(['dataset_id' => $available->id]);
        $unavailable = Dataset::factory()->create(['is_available' => false]);
        $task->datasets()->attach([$available->id, $unavailable->id]);

        // Act
        $tasks = new GetAnnotationTasksQuery()->get();

        // Assert
        expect($tasks)->toHaveCount(1);
        $loaded = $tasks->first();
        expect($loaded->tags->pluck('name')->all())->toBe(['NER'])
            ->and($loaded->datasets->pluck('id')->all())->toBe([$available->id])
            ->and($loaded->datasets->first()->instances_count)->toBe(2);
    });

    it('filters tasks to those connected to the given user', function (): void {
        // Arrange
        $user = User::factory()->create();
        $connectedTask = AnnotationTask::factory()->create();
        $connectedTask->connectedUsers()->attach($user->id);
        AnnotationTask::factory()->create(); // not connected to the user

        // Act
        $tasks = new GetAnnotationTasksQuery()->get($user->id);

        // Assert
        expect($tasks->pluck('id')->all())->toBe([$connectedTask->id]);
    });

    it('restricts datasets to those the user manages when scoped by user', function (): void {
        // Arrange
        $user = User::factory()->create();
        $task = AnnotationTask::factory()->create();
        $task->connectedUsers()->attach($user->id);

        $managed = Dataset::factory()->create(['is_available' => true]);
        $managed->connectedManagers()->attach($user->id);
        $unmanaged = Dataset::factory()->create(['is_available' => true]);
        $task->datasets()->attach([$managed->id, $unmanaged->id]);

        // Act
        $tasks = new GetAnnotationTasksQuery()->get($user->id);

        // Assert
        expect($tasks->first()->datasets->pluck('id')->all())->toBe([$managed->id]);
    });
});
