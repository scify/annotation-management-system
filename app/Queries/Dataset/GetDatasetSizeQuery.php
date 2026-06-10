<?php

declare(strict_types=1);

namespace App\Queries\Dataset;

use App\Models\DatasetInstance;

final readonly class GetDatasetSizeQuery {
    public function get(int $datasetId): int {
        return (int) DatasetInstance::query()
            ->where('dataset_id', $datasetId)
            ->count();
    }
}
