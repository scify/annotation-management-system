<?php

declare(strict_types=1);

namespace App\Queries\Manager;

use App\Models\AnnotatorOfManager;

final readonly class SyncAnnotatorsForManagerQuery {
    /**
     * @param  array<int, int>  $annotatorIds
     */
    public function sync(int $managerId, array $annotatorIds): void {
        AnnotatorOfManager::query()->where('manager_id', $managerId)->delete();

        foreach ($annotatorIds as $annotatorId) {
            AnnotatorOfManager::query()->create([
                'manager_id' => $managerId,
                'annotator_id' => $annotatorId,
            ]);
        }
    }
}
