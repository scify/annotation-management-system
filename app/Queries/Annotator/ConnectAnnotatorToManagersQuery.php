<?php

declare(strict_types=1);

namespace App\Queries\Annotator;

use App\Models\AnnotatorOfManager;

final readonly class ConnectAnnotatorToManagersQuery {
    /**
     * @param  array<int, int>  $managerIds
     */
    public function connect(int $annotatorId, array $managerIds): void {
        foreach ($managerIds as $managerId) {
            AnnotatorOfManager::query()->create([
                'manager_id' => $managerId,
                'annotator_id' => $annotatorId,
            ]);
        }
    }
}
