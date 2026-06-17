<?php

declare(strict_types=1);

use App\Models\Project;
use App\Queries\Project\StoreInstanceShuffleMappersQuery;
use Illuminate\Support\Facades\Date;

describe('StoreInstanceShuffleMappersQuery', function (): void {
    it('inserts all mapper rows', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $now = Date::now();
        $rows = [
            ['project_id' => $project->id, 'new_index' => 1, 'old_index' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['project_id' => $project->id, 'new_index' => 2, 'old_index' => 9, 'created_at' => $now, 'updated_at' => $now],
        ];

        // Act
        new StoreInstanceShuffleMappersQuery()->insert($rows);

        // Assert
        $this->assertDatabaseCount('instance_shuffle_mappers', 2);
        $this->assertDatabaseHas('instance_shuffle_mappers', [
            'project_id' => $project->id,
            'new_index' => 1,
            'old_index' => 5,
        ]);
    });

    it('is a no-op for an empty row set', function (): void {
        // Act
        new StoreInstanceShuffleMappersQuery()->insert([]);

        // Assert
        $this->assertDatabaseCount('instance_shuffle_mappers', 0);
    });
});
