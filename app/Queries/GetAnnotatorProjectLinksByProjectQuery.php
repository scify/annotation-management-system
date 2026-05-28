<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\AnnotatorOfProject;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetAnnotatorProjectLinksByProjectQuery {
    /**
     * @param  array<int, int>  $annotatorIds
     *
     * @return Collection<int, AnnotatorOfProject>
     */
    public function get(int $projectId, array $annotatorIds): Collection {
        if ($annotatorIds === []) {
            return new Collection();
        }

        return AnnotatorOfProject::query()
            ->where('project_id', $projectId)
            ->whereIn('user_id', $annotatorIds)
            ->get();
    }

    /**
     * @return Collection<int, AnnotatorOfProject>
     */
    public function getAll(int $projectId): Collection {
        return AnnotatorOfProject::query()
            ->where('project_id', $projectId)
            ->get();
    }
}
