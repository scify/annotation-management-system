<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\User;
use App\Queries\Project\StoreProjectManagerQuery;

describe('StoreProjectManagerQuery', function (): void {
    it('creates a project manager with the given acceptance state', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $user = User::factory()->create();

        // Act
        new StoreProjectManagerQuery()->create($project->id, $user->id, accepted: false);

        // Assert
        $this->assertDatabaseHas('project_managers', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'accepted' => false,
        ]);
    });

    it('defaults a created manager to accepted', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $user = User::factory()->create();

        // Act
        new StoreProjectManagerQuery()->create($project->id, $user->id);

        // Assert
        $this->assertDatabaseHas('project_managers', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'accepted' => true,
        ]);
    });

    it('is idempotent when using firstOrCreate', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $user = User::factory()->create();
        $query = new StoreProjectManagerQuery();
        $query->firstOrCreate($project->id, $user->id);

        // Act
        $query->firstOrCreate($project->id, $user->id);

        // Assert
        $this->assertDatabaseCount('project_managers', 1);
    });
});
