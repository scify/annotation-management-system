<?php

declare(strict_types=1);

use App\Models\AnnotationTask;
use App\Models\User;
use App\Queries\Manager\ConnectManagerToAnnotationTasksQuery;

describe('ConnectManagerToAnnotationTasksQuery', function (): void {
    it('inserts an annotation_task_user row per task', function (): void {
        // Arrange
        $manager = User::factory()->create();
        $tasks = AnnotationTask::factory()->count(2)->create();

        // Act
        new ConnectManagerToAnnotationTasksQuery()->connect($manager->id, $tasks->pluck('id')->all());

        // Assert
        $this->assertDatabaseCount('annotation_task_user', 2);
        foreach ($tasks as $task) {
            $this->assertDatabaseHas('annotation_task_user', [
                'annotation_task_id' => $task->id,
                'user_id' => $manager->id,
            ]);
        }
    });

    it('inserts nothing for an empty task list', function (): void {
        // Arrange
        $manager = User::factory()->create();

        // Act
        new ConnectManagerToAnnotationTasksQuery()->connect($manager->id, []);

        // Assert
        $this->assertDatabaseCount('annotation_task_user', 0);
    });
});
