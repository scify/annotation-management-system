<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\User;
use App\Queries\Project\AttachAnnotatorsToProjectQuery;

describe('AttachAnnotatorsToProjectQuery', function (): void {
    it('attaches annotators to the project with timestamps', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $annotators = User::factory()->count(2)->create();

        // Act
        new AttachAnnotatorsToProjectQuery()->attach($project->id, $annotators->pluck('id')->all());

        // Assert
        expect($project->annotators()->count())->toBe(2);
        foreach ($annotators as $annotator) {
            $this->assertDatabaseHas('annotator_of_project', [
                'project_id' => $project->id,
                'user_id' => $annotator->id,
            ]);
        }
    });

    it('does not duplicate an already-attached annotator', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $annotator = User::factory()->create();
        $query = new AttachAnnotatorsToProjectQuery();
        $query->attach($project->id, [$annotator->id]);

        // Act — attach the same annotator again
        $query->attach($project->id, [$annotator->id]);

        // Assert
        $this->assertDatabaseCount('annotator_of_project', 1);
    });

    it('handles an empty annotator list without inserting rows', function (): void {
        // Arrange
        $project = Project::factory()->create();

        // Act
        new AttachAnnotatorsToProjectQuery()->attach($project->id, []);

        // Assert
        $this->assertDatabaseCount('annotator_of_project', 0);
    });
});
