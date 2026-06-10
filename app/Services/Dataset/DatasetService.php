<?php

declare(strict_types=1);

namespace App\Services\Dataset;

use App\Queries\Dataset\GetDatasetSizeQuery;
use Illuminate\Support\Arr;

class DatasetService {
    public function __construct(
        private readonly GetDatasetSizeQuery $datasetSizeQuery,
    ) {}

    /**
     * @return list<int>
     */
    public function generateShuffledIndexArray(int $datasetId): array {
        $size = $this->datasetSizeQuery->get($datasetId);

        $indices = range(1, $size);
        $indices = Arr::shuffle($indices);

        return array_values($indices);
    }
}
