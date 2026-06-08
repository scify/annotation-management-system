<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\ProjectManager;

final readonly class GetProjectIdsManagedByUserQuery {
    /**
     * @return array<int, mixed>
     */
    public function get(int $userId, ?ProjectStatusEnum $status = null): array {
        return Project::query()
            ->whereIn('id', ProjectManager::query()->where('user_id', $userId)->select('project_id'))
            ->when($status instanceof ProjectStatusEnum, fn ($q) => $q->where('status', $status))
            ->pluck('id')
            ->all();
    }
}
