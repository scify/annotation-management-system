<?php

declare(strict_types=1);

namespace App\Queries\Annotator;

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

    /**
     * @return array<int, int>
     */
    public function getUserIds(int $projectId): array {
        /** @var array<int, int> */
        return AnnotatorOfProject::query()
            ->where('project_id', $projectId)
            ->pluck('user_id')
            ->all();
    }

    public function canFlag(int $annotatorId, int $projectId): bool {
        return (bool) AnnotatorOfProject::query()
            ->where('user_id', $annotatorId)
            ->where('project_id', $projectId)
            ->value('can_flag');
    }

    public function getByAnnotatorAndProject(int $annotatorId, int $projectId): AnnotatorOfProject {
        /** @var AnnotatorOfProject */
        return AnnotatorOfProject::query()
            ->where('user_id', $annotatorId)
            ->where('project_id', $projectId)
            ->firstOrFail();
    }

    /**
     * @param  array<int, int>  $projectIds
     *
     * @return Collection<int, AnnotatorOfProject>
     */
    public function getByProjectIds(array $projectIds): Collection {
        if ($projectIds === []) {
            return new Collection();
        }

        return AnnotatorOfProject::query()
            ->whereIn('project_id', $projectIds)
            ->get(['project_id', 'user_id']);
    }
}
