<?php

declare(strict_types=1);

namespace App\Queries\Dataset;

use App\Models\DatasetInstance;

final readonly class GetDatasetInstanceQuery {
    public function get(int $datasetId, int $index): ?DatasetInstance {
        return DatasetInstance::query()
            ->where('dataset_id', $datasetId)
            ->where('index', $index)
            ->first();
    }
}
