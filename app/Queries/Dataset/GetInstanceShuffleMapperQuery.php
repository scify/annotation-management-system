<?php

declare(strict_types=1);

namespace App\Queries\Dataset;

use App\Models\InstanceShuffleMapper;

final readonly class GetInstanceShuffleMapperQuery {
    public function get(int $projectId, int $newIndex): InstanceShuffleMapper {
        /** @var InstanceShuffleMapper */
        return InstanceShuffleMapper::query()
            ->where('project_id', $projectId)
            ->where('new_index', $newIndex)
            ->firstOrFail();
    }
}
