<?php

declare(strict_types=1);

namespace App\Services\Dataset;

use App\Models\DatasetInstance;
use App\Models\Project;
use App\Queries\Dataset\GetDatasetInstanceQuery;
use App\Queries\Dataset\GetInstanceShuffleMapperQuery;

class DatasetInstanceService {
    public function __construct(
        private readonly GetDatasetInstanceQuery $datasetInstanceQuery,
        private readonly GetInstanceShuffleMapperQuery $shuffleMapperQuery,
    ) {}

    public function getDatasetInstance(Project $project, int $index): ?DatasetInstance {
        $finalIndex = $index;

        if ($project->is_instance_shuffled) {
            $mapper = $this->shuffleMapperQuery->get($project->id, $index);
            $finalIndex = $mapper->old_index;
        }

        return $this->datasetInstanceQuery->get($project->dataset_id, $finalIndex);
    }
}
