<?php

declare(strict_types=1);

namespace App\Queries\SubProject;

use App\Models\SubProject;

final readonly class GetSubProjectByIdQuery {
    public function get(int $id): SubProject {
        /** @var SubProject */
        return SubProject::query()->findOrFail($id);
    }

    public function getWithProject(int $id): SubProject {
        /** @var SubProject */
        return SubProject::query()->with('project')->findOrFail($id);
    }

    public function getWithProjectAndAnnotationTask(int $id): SubProject {
        /** @var SubProject */
        return SubProject::query()->with('project.annotationTask')->findOrFail($id);
    }
}
