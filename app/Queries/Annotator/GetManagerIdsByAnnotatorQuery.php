<?php

declare(strict_types=1);

namespace App\Queries\Annotator;

use App\Models\AnnotatorOfManager;

final readonly class GetManagerIdsByAnnotatorQuery {
    /**
     * @return array<int, int>
     */
    public function get(int $annotatorId): array {
        /** @var array<int, int> $ids */
        $ids = AnnotatorOfManager::query()
            ->where('annotator_id', $annotatorId)
            ->pluck('manager_id')
            ->all();

        return $ids;
    }
}
