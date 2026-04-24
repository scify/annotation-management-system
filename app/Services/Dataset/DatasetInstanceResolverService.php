<?php

declare(strict_types=1);

namespace App\Services\Dataset;

use App\Models\DatasetInstance;
use App\Models\InstanceShuffleMapper;
use App\Models\Project;

class DatasetInstanceResolverService {
    public function resolve(Project $project, int $index): ?DatasetInstance {
        if (! $project->is_instance_shuffled) {
            return DatasetInstance::query()->where('dataset_id', $project->dataset_id)
                ->where('index', $index)
                ->first();
        }

        $mapper = InstanceShuffleMapper::with('datasetInstance')
            ->where('project_id', $project->id)
            ->where('new_index', $index)
            ->first();

        return $mapper?->datasetInstance;
    }
}
