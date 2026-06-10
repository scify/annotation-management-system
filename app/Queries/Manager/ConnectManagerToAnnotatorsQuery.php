<?php

declare(strict_types=1);

namespace App\Queries\Manager;

use App\Models\AnnotatorOfManager;
use Illuminate\Support\Facades\Date;

final readonly class ConnectManagerToAnnotatorsQuery {
    /**
     * @param  array<int, int>  $annotatorIds
     */
    public function connect(int $managerId, array $annotatorIds): void {
        foreach ($annotatorIds as $annotatorId) {
            AnnotatorOfManager::query()->create([
                'manager_id' => $managerId,
                'annotator_id' => $annotatorId,
            ]);
        }
    }

    /**
     * @param  array<int, int>  $annotatorIds
     */
    public function bulkConnect(int $managerId, array $annotatorIds): void {
        if ($annotatorIds === []) {
            return;
        }

        $now = Date::now();
        $rows = array_map(fn (int $annotatorId): array => [
            'manager_id' => $managerId,
            'annotator_id' => $annotatorId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $annotatorIds);

        AnnotatorOfManager::query()->insertOrIgnore($rows);
    }
}
