<?php

declare(strict_types=1);

namespace App\Services\Dataset;

use App\Models\DatasetInstance;
use Illuminate\Support\Arr;

class DatasetService {
    /**
     * @return list<int>
     */
    public function generateShuffledIndexArray(int $datasetId): array {
        $size = DatasetInstance::query()->where('dataset_id', $datasetId)->count();

        $indices = range(0, $size - 1);
        $indices = Arr::shuffle($indices);

        return array_values($indices);
    }
}
