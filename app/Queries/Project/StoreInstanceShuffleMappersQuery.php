<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Models\InstanceShuffleMapper;

final readonly class StoreInstanceShuffleMappersQuery {
    /**
     * @param  array<int, array{project_id: int, new_index: int, old_index: int, created_at: mixed, updated_at: mixed}>  $rows
     */
    public function insert(array $rows): void {
        if ($rows === []) {
            return;
        }

        InstanceShuffleMapper::query()->insert($rows);
    }
}
