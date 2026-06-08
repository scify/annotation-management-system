<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;

final readonly class UpdateProjectStatusQuery {
    public function execute(Project $project, ProjectStatusEnum $status): void {
        $project->update(['status' => $status]);
    }
}
