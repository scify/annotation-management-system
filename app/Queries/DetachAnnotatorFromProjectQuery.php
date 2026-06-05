<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\AnnotatorOfProject;

final readonly class DetachAnnotatorFromProjectQuery {
    public function detach(int $projectId, int $annotatorId): void {
        AnnotatorOfProject::query()
            ->where('project_id', $projectId)
            ->where('user_id', $annotatorId)
            ->delete();
    }
}
