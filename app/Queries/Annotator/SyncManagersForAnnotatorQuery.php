<?php

declare(strict_types=1);

namespace App\Queries\Annotator;

use App\Models\AnnotatorOfManager;

final readonly class SyncManagersForAnnotatorQuery {
    /**
     * @param  array<int, int>  $managerIds
     */
    public function sync(int $annotatorId, array $managerIds): void {
        AnnotatorOfManager::query()->where('annotator_id', $annotatorId)->delete();

        foreach ($managerIds as $managerId) {
            AnnotatorOfManager::query()->create([
                'manager_id' => $managerId,
                'annotator_id' => $annotatorId,
            ]);
        }
    }
}
