<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\SubProject;
use App\Queries\Project\CompleteSubProjectsByProjectQuery;

describe('CompleteSubProjectsByProjectQuery', function (): void {
    it('marks in-progress sub-projects of the project as completed', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $inProgress = SubProject::factory()->create([
            'project_id' => $project->id,
            'status' => ProjectStatusEnum::IN_PROGRESS,
        ]);

        // Act
        new CompleteSubProjectsByProjectQuery()->execute($project->id);

        // Assert
        $this->assertDatabaseHas('sub_projects', [
            'id' => $inProgress->id,
            'status' => ProjectStatusEnum::COMPLETED->value,
        ]);
    });

    it('leaves non-in-progress sub-projects untouched', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $pending = SubProject::factory()->create([
            'project_id' => $project->id,
            'status' => ProjectStatusEnum::PENDING,
        ]);

        // Act
        new CompleteSubProjectsByProjectQuery()->execute($project->id);

        // Assert
        $this->assertDatabaseHas('sub_projects', [
            'id' => $pending->id,
            'status' => ProjectStatusEnum::PENDING->value,
        ]);
    });

    it('does not affect sub-projects of other projects', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $otherSubProject = SubProject::factory()->create([
            'project_id' => Project::factory(),
            'status' => ProjectStatusEnum::IN_PROGRESS,
        ]);

        // Act
        new CompleteSubProjectsByProjectQuery()->execute($project->id);

        // Assert
        $this->assertDatabaseHas('sub_projects', [
            'id' => $otherSubProject->id,
            'status' => ProjectStatusEnum::IN_PROGRESS->value,
        ]);
    });
});
