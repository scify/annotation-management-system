<?php

declare(strict_types=1);

use App\Models\Dataset;
use App\Models\User;
use App\Queries\Manager\ConnectManagerToDatasetsQuery;

describe('ConnectManagerToDatasetsQuery', function (): void {
    it('inserts a dataset_user row per dataset', function (): void {
        // Arrange
        $manager = User::factory()->create();
        $datasets = Dataset::factory()->count(2)->create();

        // Act
        new ConnectManagerToDatasetsQuery()->connect($manager->id, $datasets->pluck('id')->all());

        // Assert
        $this->assertDatabaseCount('dataset_user', 2);
        foreach ($datasets as $dataset) {
            $this->assertDatabaseHas('dataset_user', [
                'dataset_id' => $dataset->id,
                'user_id' => $manager->id,
            ]);
        }
    });

    it('inserts nothing for an empty dataset list', function (): void {
        // Arrange
        $manager = User::factory()->create();

        // Act
        new ConnectManagerToDatasetsQuery()->connect($manager->id, []);

        // Assert
        $this->assertDatabaseCount('dataset_user', 0);
    });
});
