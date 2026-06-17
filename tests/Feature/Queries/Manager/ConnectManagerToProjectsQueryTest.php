<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\User;
use App\Queries\Manager\ConnectManagerToProjectsQuery;

describe('ConnectManagerToProjectsQuery', function (): void {
    it('creates an accepted project manager row per project', function (): void {
        // Arrange
        $manager = User::factory()->create();
        $projects = Project::factory()->count(2)->create();

        // Act
        new ConnectManagerToProjectsQuery()->connect($manager->id, $projects->pluck('id')->all());

        // Assert
        $this->assertDatabaseCount('project_managers', 2);
        foreach ($projects as $project) {
            $this->assertDatabaseHas('project_managers', [
                'project_id' => $project->id,
                'user_id' => $manager->id,
                'accepted' => true,
            ]);
        }
    });

    it('inserts nothing for an empty project list', function (): void {
        // Arrange
        $manager = User::factory()->create();

        // Act
        new ConnectManagerToProjectsQuery()->connect($manager->id, []);

        // Assert
        $this->assertDatabaseCount('project_managers', 0);
    });
});
