<?php

declare(strict_types=1);

namespace App\Queries\Manager;

use App\Models\ProjectManager;

final readonly class GetConnectedProjectIdsByUserQuery {
    /**
     * @return array<int, int>
     */
    public function get(int $userId): array {
        /** @var array<int, int> $ids */
        $ids = ProjectManager::query()
            ->where('user_id', $userId)
            ->pluck('project_id')
            ->all();

        return $ids;
    }
}
