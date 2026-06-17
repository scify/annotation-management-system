<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\User;
use App\Queries\Project\GetProjectsQuery;

describe('GetProjectsQuery', function (): void {
    it('returns all project ids via getIds', function (): void {
        // Arrange
        $projects = Project::factory()->count(3)->create();

        // Act
        $ids = new GetProjectsQuery()->getIds();

        // Assert
        expect($ids)->toEqualCanonicalizing($projects->pluck('id')->all());
    });

    it('filters ids by status', function (): void {
        // Arrange
        $inProgress = Project::factory()->create(['status' => ProjectStatusEnum::IN_PROGRESS]);
        Project::factory()->create(['status' => ProjectStatusEnum::COMPLETED]);

        // Act
        $ids = new GetProjectsQuery()->getIds(ProjectStatusEnum::IN_PROGRESS);

        // Assert
        expect($ids)->toBe([$inProgress->id]);
    });

    it('returns all projects with the annotators count', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $project->annotators()->attach(User::factory()->count(2)->create()->pluck('id')->all());

        // Act
        $projects = new GetProjectsQuery()->get();

        // Assert
        expect($projects)->toHaveCount(1)
            ->and($projects->first()->annotators_count)->toBe(2);
    });

    it('filters the project collection by status', function (): void {
        // Arrange
        $inProgress = Project::factory()->create(['status' => ProjectStatusEnum::IN_PROGRESS]);
        Project::factory()->create(['status' => ProjectStatusEnum::COMPLETED]);

        // Act
        $projects = new GetProjectsQuery()->get(ProjectStatusEnum::IN_PROGRESS);

        // Assert
        expect($projects->pluck('id')->all())->toBe([$inProgress->id]);
    });
});
