<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Models\Project;

final readonly class GetProjectByIdQuery {
    public function get(int $id): Project {
        /** @var Project */
        return Project::query()->findOrFail($id);
    }
}
