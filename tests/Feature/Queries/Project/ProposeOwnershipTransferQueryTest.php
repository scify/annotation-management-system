<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\User;
use App\Queries\Project\ProposeOwnershipTransferQuery;

describe('ProposeOwnershipTransferQuery', function (): void {
    it('reports no active proposal when none exists', function (): void {
        // Arrange
        $manager = ProjectManager::factory()->create([
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'proposed_to_become_owner' => false,
        ]);

        // Act
        $hasProposal = new ProposeOwnershipTransferQuery()->hasActiveProposal($manager->project_id);

        // Assert
        expect($hasProposal)->toBeFalse();
    });

    it('reports an active proposal when a manager is flagged', function (): void {
        // Arrange
        $manager = ProjectManager::factory()->create([
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'proposed_to_become_owner' => true,
        ]);

        // Act
        $hasProposal = new ProposeOwnershipTransferQuery()->hasActiveProposal($manager->project_id);

        // Assert
        expect($hasProposal)->toBeTrue();
    });

    it('flags the given manager as the proposed owner', function (): void {
        // Arrange
        $manager = ProjectManager::factory()->create([
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'proposed_to_become_owner' => false,
        ]);

        // Act
        new ProposeOwnershipTransferQuery()->execute($manager->project_id, $manager->user_id);

        // Assert
        $this->assertDatabaseHas('project_managers', [
            'id' => $manager->id,
            'proposed_to_become_owner' => true,
        ]);
    });
});
