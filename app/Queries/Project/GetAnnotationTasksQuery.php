<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Models\AnnotationTask;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;

final readonly class GetAnnotationTasksQuery {
    /**
     * @return Collection<int, AnnotationTask>
     */
    public function get(?int $userId = null): Collection {
        return AnnotationTask::query()
            ->select(['id', 'title', 'description', 'short_description', 'guidelines_url', 'customization_options'])
            ->when($userId !== null, function ($query) use ($userId): void {
                $query->whereIn('id', function (QueryBuilder $q) use ($userId): void {
                    $q->select('annotation_task_id')
                        ->from('annotation_task_user')
                        ->where('user_id', $userId);
                });
            })
            ->with('tags:id,name')
            ->with(['datasets' => function ($query) use ($userId): void {
                $query->select(['datasets.id', 'datasets.name', 'datasets.description'])
                    ->withCount('instances')
                    ->where('datasets.is_available', true)
                    ->when(
                        $userId !== null,
                        fn ($q) => $q->whereRelation('connectedManagers', 'users.id', $userId),
                    );
            }])
            ->get();
    }
}
