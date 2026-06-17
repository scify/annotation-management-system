<?php

declare(strict_types=1);

use App\Models\Project;
use App\Queries\Project\GetProjectBasicDataQuery;
use Illuminate\Database\Eloquent\ModelNotFoundException;

describe('GetProjectBasicDataQuery', function (): void {
    it('returns the project id and name', function (): void {
        // Arrange
        $project = Project::factory()->create(['name' => 'Climate Corpus']);

        // Act
        $data = new GetProjectBasicDataQuery()->get($project->id);

        // Assert
        expect($data)->toBe([
            'project_id' => $project->id,
            'name' => 'Climate Corpus',
        ]);
    });

    it('throws when the project does not exist', function (): void {
        // Act & Assert
        expect(fn (): array => new GetProjectBasicDataQuery()->get(999999))
            ->toThrow(ModelNotFoundException::class);
    });
});
