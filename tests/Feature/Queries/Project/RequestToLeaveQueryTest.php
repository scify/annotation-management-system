<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\User;
use App\Queries\Project\RequestToLeaveQuery;

describe('RequestToLeaveQuery', function (): void {
    it('flags the manager as requesting to leave', function (): void {
        // Arrange
        $manager = ProjectManager::factory()->create([
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'request_to_leave' => false,
        ]);

        // Act
        new RequestToLeaveQuery()->execute($manager->project_id, $manager->user_id);

        // Assert
        $this->assertDatabaseHas('project_managers', [
            'id' => $manager->id,
            'request_to_leave' => true,
        ]);
    });

    it('clears the request-to-leave flag', function (): void {
        // Arrange
        $manager = ProjectManager::factory()->create([
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'request_to_leave' => true,
        ]);

        // Act
        new RequestToLeaveQuery()->clear($manager->project_id, $manager->user_id);

        // Assert
        $this->assertDatabaseHas('project_managers', [
            'id' => $manager->id,
            'request_to_leave' => false,
        ]);
    });
});
