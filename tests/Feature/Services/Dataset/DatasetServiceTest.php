<?php

declare(strict_types=1);

use App\Models\Dataset;
use App\Services\Dataset\DatasetService;
use Database\Seeders\DummyDataSeeder;

describe('DatasetService::generateShuffledIndexArray', function (): void {
    beforeEach(function (): void {
        $this->seed(DummyDataSeeder::class);
        $this->dataset = Dataset::query()->first();
    });

    it('returns a permutation of 0..size-1 matching the number of dataset instances', function (): void {

        // Act
        $result = new DatasetService()->generateShuffledIndexArray($this->dataset->id);

        // Assert
        $sorted = $result;
        sort($sorted);

        expect($result)->toHaveCount(5)
            ->and($sorted)->toBe([0, 1, 2, 3, 4]);
    });
});
