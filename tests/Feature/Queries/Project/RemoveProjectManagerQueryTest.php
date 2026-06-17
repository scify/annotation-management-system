<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\User;
use App\Queries\Project\RemoveProjectManagerQuery;

describe('RemoveProjectManagerQuery', function (): void {
    it('removes the matching project manager row', function (): void {
        // Arrange
        $manager = ProjectManager::factory()->create([
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
        ]);

        // Act
        new RemoveProjectManagerQuery()->execute($manager->project_id, $manager->user_id);

        // Assert
        $this->assertDatabaseMissing('project_managers', ['id' => $manager->id]);
    });

    it('does not remove managers of other projects or users', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $target = ProjectManager::factory()->create(['project_id' => $project->id, 'user_id' => User::factory()]);
        $sameProjectOther = ProjectManager::factory()->create(['project_id' => $project->id, 'user_id' => User::factory()]);

        // Act
        new RemoveProjectManagerQuery()->execute($target->project_id, $target->user_id);

        // Assert
        $this->assertDatabaseHas('project_managers', ['id' => $sameProjectOther->id]);
        $this->assertDatabaseCount('project_managers', 1);
    });
});
