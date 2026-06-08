<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Models\Project;
use App\Models\SubProject;

final readonly class GetSubsetInfoByProjectQuery {
    /**
     * @return array{dataset_id: int, dataset_name: string, size: int, previous_subset_last_index: int|null}
     */
    public function get(int $projectId): array {
        $project = Project::query()
            ->with('dataset:id,name,size')
            ->findOrFail($projectId);

        /** @var SubProject|null $latestSubProject */
        $latestSubProject = SubProject::query()
            ->where('project_id', $projectId)
            ->latest()
            ->first();

        return [
            'dataset_id' => $project->dataset->id,
            'dataset_name' => $project->dataset->name,
            'size' => $project->dataset->size,
            'previous_subset_last_index' => $latestSubProject?->last_instance_index,
        ];
    }
}
