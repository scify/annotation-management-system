<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Queries\Project\UpdateProjectStatusQuery;

describe('UpdateProjectStatusQuery', function (): void {
    it('updates the status of the given project', function (): void {
        // Arrange
        $project = Project::factory()->create(['status' => ProjectStatusEnum::PENDING]);

        // Act
        new UpdateProjectStatusQuery()->execute($project, ProjectStatusEnum::COMPLETED);

        // Assert
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => ProjectStatusEnum::COMPLETED->value,
        ]);
    });
});
