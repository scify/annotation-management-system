<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\SubProject;
use App\Queries\Project\GetProjectsByIdsQuery;
use Illuminate\Database\Eloquent\Collection;

describe('GetProjectsByIdsQuery', function (): void {
    it('returns an empty collection for an empty id list', function (): void {
        // Act
        $projects = new GetProjectsByIdsQuery()->get([]);

        // Assert
        expect($projects)->toBeInstanceOf(Collection::class)->toBeEmpty();
    });

    it('returns the projects matching the given ids with relations eager loaded', function (): void {
        // Arrange
        $project = Project::factory()->create();
        SubProject::factory()->create(['project_id' => $project->id]);
        Project::factory()->create(); // unrelated project

        // Act
        $projects = new GetProjectsByIdsQuery()->get([$project->id]);

        // Assert
        expect($projects)->toHaveCount(1);
        $loaded = $projects->first();
        expect($loaded->id)->toBe($project->id)
            ->and($loaded->relationLoaded('owner'))->toBeTrue()
            ->and($loaded->relationLoaded('annotationTask'))->toBeTrue()
            ->and($loaded->relationLoaded('dataset'))->toBeTrue()
            ->and($loaded->relationLoaded('subProjects'))->toBeTrue()
            ->and($loaded->subProjects)->toHaveCount(1);
    });

    it('filters by status when provided', function (): void {
        // Arrange
        $inProgress = Project::factory()->create(['status' => ProjectStatusEnum::IN_PROGRESS]);
        $completed = Project::factory()->create(['status' => ProjectStatusEnum::COMPLETED]);

        // Act
        $projects = new GetProjectsByIdsQuery()->get([$inProgress->id, $completed->id], ProjectStatusEnum::COMPLETED);

        // Assert
        expect($projects->pluck('id')->all())->toBe([$completed->id]);
    });
});
