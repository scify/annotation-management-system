<?php

declare(strict_types=1);

use App\Models\Dataset;
use App\Models\Project;
use App\Models\SubProject;
use App\Queries\Project\GetSubsetInfoByProjectQuery;
use Illuminate\Database\Eloquent\ModelNotFoundException;

describe('GetSubsetInfoByProjectQuery', function (): void {
    it('returns dataset info with the last index of the most recent sub-project', function (): void {
        // Arrange
        $dataset = Dataset::factory()->create(['name' => 'Tweets', 'size' => 500]);
        $project = Project::factory()->create(['dataset_id' => $dataset->id]);
        SubProject::factory()->create(['project_id' => $project->id, 'last_instance_index' => 120]);

        // Act
        $info = new GetSubsetInfoByProjectQuery()->get($project->id);

        // Assert
        expect($info)->toBe([
            'dataset_id' => $dataset->id,
            'dataset_name' => 'Tweets',
            'size' => 500,
            'previous_subset_last_index' => 120,
        ]);
    });

    it('returns a null previous index when the project has no sub-projects', function (): void {
        // Arrange
        $dataset = Dataset::factory()->create(['size' => 300]);
        $project = Project::factory()->create(['dataset_id' => $dataset->id]);

        // Act
        $info = new GetSubsetInfoByProjectQuery()->get($project->id);

        // Assert
        expect($info['previous_subset_last_index'])->toBeNull()
            ->and($info['size'])->toBe(300);
    });

    it('throws when the project does not exist', function (): void {
        // Act & Assert
        expect(fn (): array => new GetSubsetInfoByProjectQuery()->get(999999))
            ->toThrow(ModelNotFoundException::class);
    });
});
