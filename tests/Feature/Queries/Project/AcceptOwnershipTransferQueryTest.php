<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\User;
use App\Queries\Project\AcceptOwnershipTransferQuery;

describe('AcceptOwnershipTransferQuery', function (): void {
    it('clears the ownership proposal flag for the given manager', function (): void {
        // Arrange
        $manager = ProjectManager::factory()->create([
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'proposed_to_become_owner' => true,
        ]);

        // Act
        new AcceptOwnershipTransferQuery()->clearProposal($manager->project_id, $manager->user_id);

        // Assert
        $this->assertDatabaseHas('project_managers', [
            'id' => $manager->id,
            'proposed_to_become_owner' => false,
        ]);
    });

    it('only clears the proposal for the matching project and user', function (): void {
        // Arrange
        $target = ProjectManager::factory()->create([
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'proposed_to_become_owner' => true,
        ]);
        $other = ProjectManager::factory()->create([
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'proposed_to_become_owner' => true,
        ]);

        // Act
        new AcceptOwnershipTransferQuery()->clearProposal($target->project_id, $target->user_id);

        // Assert
        $this->assertDatabaseHas('project_managers', ['id' => $other->id, 'proposed_to_become_owner' => true]);
    });

    it('transfers ownership of the project to the given user', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $newOwner = User::factory()->create();

        // Act
        new AcceptOwnershipTransferQuery()->transferOwner($project->id, $newOwner->id);

        // Assert
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'owner_user_id' => $newOwner->id,
        ]);
    });
});
