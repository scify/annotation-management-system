<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\User;
use App\Queries\Project\GetProjectsManagedByUserQuery;

describe('GetProjectsManagedByUserQuery', function (): void {
    it('returns only projects managed by the user with the annotators count', function (): void {
        // Arrange
        $user = User::factory()->create();
        $managed = Project::factory()->create();
        Project::factory()->create(); // unmanaged
        ProjectManager::factory()->create(['project_id' => $managed->id, 'user_id' => $user->id]);
        $managed->annotators()->attach(User::factory()->count(3)->create()->pluck('id')->all());

        // Act
        $projects = new GetProjectsManagedByUserQuery()->get($user->id);

        // Assert
        expect($projects)->toHaveCount(1)
            ->and($projects->first()->id)->toBe($managed->id)
            ->and($projects->first()->annotators_count)->toBe(3);
    });

    it('filters managed projects by status when provided', function (): void {
        // Arrange
        $user = User::factory()->create();
        $inProgress = Project::factory()->create(['status' => ProjectStatusEnum::IN_PROGRESS]);
        $completed = Project::factory()->create(['status' => ProjectStatusEnum::COMPLETED]);
        ProjectManager::factory()->create(['project_id' => $inProgress->id, 'user_id' => $user->id]);
        ProjectManager::factory()->create(['project_id' => $completed->id, 'user_id' => $user->id]);

        // Act
        $projects = new GetProjectsManagedByUserQuery()->get($user->id, ProjectStatusEnum::COMPLETED);

        // Assert
        expect($projects->pluck('id')->all())->toBe([$completed->id]);
    });
});
