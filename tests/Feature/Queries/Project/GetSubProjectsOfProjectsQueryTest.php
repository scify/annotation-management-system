<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\SubProject;
use App\Queries\Project\GetSubProjectsOfProjectsQuery;
use Illuminate\Database\Eloquent\Collection;

describe('GetSubProjectsOfProjectsQuery', function (): void {
    it('returns an empty collection for an empty project id list', function (): void {
        // Act
        $subProjects = new GetSubProjectsOfProjectsQuery()->get([]);

        // Assert
        expect($subProjects)->toBeInstanceOf(Collection::class)->toBeEmpty();
    });

    it('returns sub-projects scoped to the given projects', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $subProject = SubProject::factory()->create(['project_id' => $project->id]);
        SubProject::factory()->create(['project_id' => Project::factory()]); // other project

        // Act
        $subProjects = new GetSubProjectsOfProjectsQuery()->get([$project->id]);

        // Assert
        expect($subProjects->pluck('id')->all())->toBe([$subProject->id]);
    });

    it('filters by status when provided', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $inProgress = SubProject::factory()->create(['project_id' => $project->id, 'status' => ProjectStatusEnum::IN_PROGRESS]);
        SubProject::factory()->create(['project_id' => $project->id, 'status' => ProjectStatusEnum::COMPLETED]);

        // Act
        $subProjects = new GetSubProjectsOfProjectsQuery()->get([$project->id], ProjectStatusEnum::IN_PROGRESS);

        // Assert
        expect($subProjects->pluck('id')->all())->toBe([$inProgress->id]);
    });
});
