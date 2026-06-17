<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\User;
use App\Queries\Project\DetachAnnotatorFromProjectQuery;

describe('DetachAnnotatorFromProjectQuery', function (): void {
    it('detaches the matching annotator from the project', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $annotator = User::factory()->create();
        $project->annotators()->attach($annotator->id);

        // Act
        new DetachAnnotatorFromProjectQuery()->detach($project->id, $annotator->id);

        // Assert
        $this->assertDatabaseMissing('annotator_of_project', [
            'project_id' => $project->id,
            'user_id' => $annotator->id,
        ]);
    });

    it('leaves other annotators on the project attached', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $toRemove = User::factory()->create();
        $toKeep = User::factory()->create();
        $project->annotators()->attach([$toRemove->id, $toKeep->id]);

        // Act
        new DetachAnnotatorFromProjectQuery()->detach($project->id, $toRemove->id);

        // Assert
        $this->assertDatabaseHas('annotator_of_project', [
            'project_id' => $project->id,
            'user_id' => $toKeep->id,
        ]);
        $this->assertDatabaseCount('annotator_of_project', 1);
    });
});
