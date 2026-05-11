<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Enums\RolesEnum;
use App\Models\AnnotationTask;
use App\Models\TaskTag;
use App\Models\User;
use App\Queries\GetAnnotationTasksQuery;

readonly class ProjectService {
    public function __construct(
        private GetAnnotationTasksQuery $getAnnotationTasksQuery,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getDataForCreateProject(User $user): array {
        return [
            'annotation_tasks' => $this->getAnnotationTasks($user),
        ];
    }

    /**
     * Returns annotation tasks the given user is allowed to see:
     *   - ADMIN            → all tasks
     *   - ANNOTATION_MANAGER → only tasks linked via annotation_task_user
     *   - ANNOTATOR        → none
     *
     * @return array<int, array<string, mixed>>
     */
    private function getAnnotationTasks(User $user): array {
        $roleName = $user->getRoleNames()->first();

        if ($roleName === RolesEnum::ANNOTATOR->value) {
            return [];
        }

        $userId = $roleName === RolesEnum::ANNOTATION_MANAGER->value ? $user->id : null;

        return $this->getAnnotationTasksQuery->get($userId)
            ->map(fn (AnnotationTask $task): array => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'short_description' => $task->short_description,
                'guidelines_url' => $task->guidelines_url,
                'tags' => $this->formatTags($task),
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function formatTags(AnnotationTask $task): array {
        return $task->tags
            ->take(4)
            ->map(fn (TaskTag $tag): array => ['id' => $tag->id, 'name' => $tag->name])
            ->values()
            ->all();
    }
}
