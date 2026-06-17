<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\User;
use App\Queries\Project\GetManagerIdsByProjectsQuery;

describe('GetManagerIdsByProjectsQuery', function (): void {
    it('returns an empty array when no project ids are given', function (): void {
        // Act
        $ids = new GetManagerIdsByProjectsQuery()->get([], excludeUserId: 1);

        // Assert
        expect($ids)->toBe([]);
    });

    it('returns unique manager ids excluding the given user', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $managerA = User::factory()->create();
        $managerB = User::factory()->create();
        $excluded = User::factory()->create();
        foreach ([$managerA, $managerB, $excluded] as $user) {
            ProjectManager::factory()->create(['project_id' => $project->id, 'user_id' => $user->id]);
        }

        // Act
        $ids = new GetManagerIdsByProjectsQuery()->get([$project->id], excludeUserId: $excluded->id);

        // Assert
        expect($ids)->toEqualCanonicalizing([$managerA->id, $managerB->id])
            ->and($ids)->not->toContain($excluded->id);
    });

    it('deduplicates managers spanning multiple projects', function (): void {
        // Arrange
        $projectA = Project::factory()->create();
        $projectB = Project::factory()->create();
        $manager = User::factory()->create();
        ProjectManager::factory()->create(['project_id' => $projectA->id, 'user_id' => $manager->id]);
        ProjectManager::factory()->create(['project_id' => $projectB->id, 'user_id' => $manager->id]);

        // Act
        $ids = new GetManagerIdsByProjectsQuery()->get([$projectA->id, $projectB->id], excludeUserId: 0);

        // Assert
        expect($ids)->toBe([$manager->id]);
    });
});
