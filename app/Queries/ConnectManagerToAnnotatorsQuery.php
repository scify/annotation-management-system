<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\AnnotatorOfManager;

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
}
