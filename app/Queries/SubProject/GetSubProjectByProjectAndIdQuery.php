<?php

declare(strict_types=1);

namespace App\Queries\SubProject;

use App\Models\SubProject;

final readonly class GetSubProjectByProjectAndIdQuery {
    public function getForEdit(int $projectId, int $subProjectId): SubProject {
        /** @var SubProject */
        return SubProject::query()
            ->with(['project.annotationTask', 'project.dataset:id,name'])
            ->where('project_id', $projectId)
            ->findOrFail($subProjectId);
    }

    public function getForAddAnnotators(int $projectId, int $subProjectId): SubProject {
        /** @var SubProject */
        return SubProject::query()
            ->select(['id', 'project_id', 'name'])
            ->with('project:id,name')
            ->where('project_id', $projectId)
            ->findOrFail($subProjectId);
    }
}
