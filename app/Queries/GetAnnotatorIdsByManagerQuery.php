<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\AnnotatorOfManager;

final readonly class GetAnnotatorIdsByManagerQuery {
    /**
     * @return array<int, int>
     */
    public function get(int $managerId): array {
        /** @var array<int, int> $ids */
        $ids = AnnotatorOfManager::query()
            ->where('manager_id', $managerId)
            ->pluck('annotator_id')
            ->all();

        return $ids;
    }
}
