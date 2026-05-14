<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\ProjectManager;

final readonly class GetProjectIdsByManagerQuery {
    /**
     * @return array<int, mixed>
     */
    public function get(int $userId): array {
        return ProjectManager::query()
            ->where('user_id', $userId)
            ->pluck('project_id')
            ->all();
    }
}
