<?php

declare(strict_types=1);

namespace App\Services\Dataset;

use App\Models\DatasetInstance;
use App\Models\InstanceShuffleMapper;
use App\Models\Project;

class DatasetInstanceService {
    public function getDatasetInstance(Project $project, int $index): ?DatasetInstance {
        $final_index = $index;
        if ($project->is_instance_shuffled) {
            $mapper = InstanceShuffleMapper::with('datasetInstance')
                ->where('project_id', $project->id)
                ->where('new_index', $index)
                ->first();
            $final_index = $mapper->old_index;
        }

        return DatasetInstance::query()->where('dataset_id', $project->dataset_id)
            ->where('index', $final_index)
            ->first();
    }
}
