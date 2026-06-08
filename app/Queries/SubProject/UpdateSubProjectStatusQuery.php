<?php

declare(strict_types=1);

namespace App\Queries\SubProject;

use App\Enums\ProjectStatusEnum;
use App\Models\SubProject;

final readonly class UpdateSubProjectStatusQuery {
    public function execute(SubProject $subProject, ProjectStatusEnum $status): void {
        $subProject->update(['status' => $status]);
    }
}
