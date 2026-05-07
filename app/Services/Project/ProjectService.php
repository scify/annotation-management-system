<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Enums\RolesEnum;
use App\Models\AnnotationTask;
use App\Models\TaskTag;
use App\Models\User;
use Illuminate\Database\Query\Builder as QueryBuilder;

readonly class ProjectService {
    /**
     * Returns annotation tasks the given user is allowed to see:
     *   - ADMIN            → all tasks
     *   - ANNOTATION_MANAGER → only tasks linked via annotation_task_user
     *   - ANNOTATOR        → none
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAnnotationTasks(User $user): array {
        $roleName = $user->getRoleNames()->first();

        if ($roleName === RolesEnum::ANNOTATOR->value) {
            return [];
        }

        $query = AnnotationTask::query()
            ->select(['id', 'title', 'description', 'short_description', 'guidelines_url']);

        if ($roleName === RolesEnum::ANNOTATION_MANAGER->value) {
            $query->whereIn('id', function (QueryBuilder $q) use ($user): void {
                $q->select('annotation_task_id')
                    ->from('annotation_task_user')
                    ->where('user_id', $user->id);
            });
        }

        $tasks = $query->get()
            ->map(fn (AnnotationTask $task): array => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'short_description' => $task->short_description,
                'guidelines_url' => $task->guidelines_url,
                'tags' => [],
            ])
            ->all();

        if ($tasks === []) {
            return [];
        }

        $taskIds = array_column($tasks, 'id');

        $tagsByTaskId = TaskTag::query()
            ->join('annotation_task_task_tag', 'annotation_task_task_tag.task_tag_id', '=', 'task_tags.id')
            ->whereIn('annotation_task_task_tag.annotation_task_id', $taskIds)
            ->select(['annotation_task_task_tag.annotation_task_id as pivot_task_id', 'task_tags.id', 'task_tags.name'])
            ->get()
            ->groupBy('pivot_task_id');

        foreach ($tasks as &$task) {
            $task['tags'] = ($tagsByTaskId->get((int) $task['id']) ?? collect())
                ->map(fn (TaskTag $tag): array => ['id' => $tag->id, 'name' => $tag->name])
                ->values()
                ->all();
        }

        return array_values($tasks);
    }
}
