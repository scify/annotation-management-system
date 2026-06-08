<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Enums\ProjectStatusEnum;
use App\Models\SubProject;

final readonly class CompleteSubProjectsByProjectQuery {
    public function execute(int $projectId): void {
        SubProject::query()
            ->where('project_id', $projectId)
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->update(['status' => ProjectStatusEnum::COMPLETED]);
    }
}
