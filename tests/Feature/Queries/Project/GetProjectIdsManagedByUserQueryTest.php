<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\User;
use App\Queries\Project\GetProjectIdsManagedByUserQuery;

describe('GetProjectIdsManagedByUserQuery', function (): void {
    it('returns only the ids of projects the user manages', function (): void {
        // Arrange
        $user = User::factory()->create();
        $managed = Project::factory()->create();
        $unmanaged = Project::factory()->create();
        ProjectManager::factory()->create(['project_id' => $managed->id, 'user_id' => $user->id]);

        // Act
        $ids = new GetProjectIdsManagedByUserQuery()->get($user->id);

        // Assert
        expect($ids)->toBe([$managed->id])
            ->and($ids)->not->toContain($unmanaged->id);
    });

    it('narrows the results by status when provided', function (): void {
        // Arrange
        $user = User::factory()->create();
        $inProgress = Project::factory()->create(['status' => ProjectStatusEnum::IN_PROGRESS]);
        $completed = Project::factory()->create(['status' => ProjectStatusEnum::COMPLETED]);
        ProjectManager::factory()->create(['project_id' => $inProgress->id, 'user_id' => $user->id]);
        ProjectManager::factory()->create(['project_id' => $completed->id, 'user_id' => $user->id]);

        // Act
        $ids = new GetProjectIdsManagedByUserQuery()->get($user->id, ProjectStatusEnum::IN_PROGRESS);

        // Assert
        expect($ids)->toBe([$inProgress->id]);
    });
});
