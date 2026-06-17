<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\SubProject;
use App\Queries\Project\GetSubProjectIdsQuery;

describe('GetSubProjectIdsQuery', function (): void {
    it('returns an empty collection for an empty project id list', function (): void {
        // Act
        $ids = new GetSubProjectIdsQuery()->get([]);

        // Assert
        expect($ids)->toBeEmpty();
    });

    it('returns sub-project ids scoped to the given projects', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $subProject = SubProject::factory()->create(['project_id' => $project->id]);
        SubProject::factory()->create(['project_id' => Project::factory()]); // other project

        // Act
        $ids = new GetSubProjectIdsQuery()->get([$project->id]);

        // Assert
        expect($ids->all())->toBe([$subProject->id]);
    });

    it('filters scoped sub-projects by status', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $inProgress = SubProject::factory()->create(['project_id' => $project->id, 'status' => ProjectStatusEnum::IN_PROGRESS]);
        SubProject::factory()->create(['project_id' => $project->id, 'status' => ProjectStatusEnum::COMPLETED]);

        // Act
        $ids = new GetSubProjectIdsQuery()->get([$project->id], ProjectStatusEnum::IN_PROGRESS);

        // Assert
        expect($ids->all())->toBe([$inProgress->id]);
    });

    it('returns all sub-project ids regardless of project via getAll', function (): void {
        // Arrange
        $a = SubProject::factory()->create(['project_id' => Project::factory()]);
        $b = SubProject::factory()->create(['project_id' => Project::factory()]);

        // Act
        $ids = new GetSubProjectIdsQuery()->getAll();

        // Assert
        expect($ids->all())->toEqualCanonicalizing([$a->id, $b->id]);
    });

    it('filters getAll results by status', function (): void {
        // Arrange
        $inProgress = SubProject::factory()->create(['project_id' => Project::factory(), 'status' => ProjectStatusEnum::IN_PROGRESS]);
        SubProject::factory()->create(['project_id' => Project::factory(), 'status' => ProjectStatusEnum::COMPLETED]);

        // Act
        $ids = new GetSubProjectIdsQuery()->getAll(ProjectStatusEnum::IN_PROGRESS);

        // Assert
        expect($ids->all())->toBe([$inProgress->id]);
    });
});
