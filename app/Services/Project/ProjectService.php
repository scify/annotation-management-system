<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Enums\RolesEnum;
use App\Models\AnnotationTask;
use App\Models\Comanager;
use App\Models\Dataset;
use App\Models\Project;
use App\Models\TaskTag;
use App\Models\User;
use App\Queries\GetActiveCoManagersQuery;
use App\Queries\GetAnnotationTasksQuery;
use App\Queries\GetAnnotatorIdsByProjectsQuery;
use App\Queries\GetCoManagersByIdsQuery;
use App\Queries\GetUserInProgressProjectsQuery;
use App\Services\Annotator\AnnotatorService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

readonly class ProjectService {
    public function __construct(
        private AnnotatorService $annotatorService,
        private GetActiveCoManagersQuery $activeCoManagersQuery,
        private GetAnnotationTasksQuery $getAnnotationTasksQuery,
        private GetAnnotatorIdsByProjectsQuery $annotatorIdsByProjectsQuery,
        private GetCoManagersByIdsQuery $coManagersByIdsQuery,
        private GetUserInProgressProjectsQuery $userInProgressProjectsQuery,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getDataForCreateProject(User $user): array {
        return [
            'annotation_tasks' => $this->getAnnotationTasks($user),
            ...$this->getAnnotatorData($user),
            ...$this->getCoManagerData($user),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getAnnotatorData(User $user): array {
        $roleName = $user->getRoleNames()->first();

        if ($roleName === RolesEnum::ANNOTATOR->value) {
            return [];
        }

        $myProjectIds = $this->userInProgressProjectsQuery->get($user->id)->pluck('id')->all();

        if ($roleName === RolesEnum::ADMIN->value) {
            $allAnnotators = $this->annotatorService->getAllAnnotators();

            return [
                'all_annotators' => $allAnnotators,
                'my_annotators' => $this->resolveMyAnnotators($myProjectIds, $allAnnotators),
            ];
        }

        return [
            'my_annotators' => $this->resolveMyAnnotators($myProjectIds),
        ];
    }

    /**
     * @param  array<int, mixed>  $projectIds
     * @param  array<int, array<string, mixed>>|null  $allAnnotators
     *
     * @return array<int, array<string, mixed>>
     */
    private function resolveMyAnnotators(array $projectIds, ?array $allAnnotators = null): array {
        if ($projectIds === []) {
            return [];
        }

        $annotatorIds = $this->annotatorIdsByProjectsQuery->get($projectIds);

        if ($annotatorIds === []) {
            return [];
        }

        if ($allAnnotators !== null) {
            $annotatorIds = array_map(fn (mixed $id): int => (int) $id, $annotatorIds);

            return array_values(array_filter(
                $allAnnotators,
                fn (array $annotator): bool => in_array((int) $annotator['id'], $annotatorIds, true),
            ));
        }

        return $this->annotatorService->getAnnotatorsByIds($annotatorIds);
    }

    /**
     * @return array<string, mixed>
     */
    private function getCoManagerData(User $user): array {
        $roleName = $user->getRoleNames()->first();

        if ($roleName === RolesEnum::ANNOTATOR->value) {
            return [];
        }

        if ($roleName === RolesEnum::ADMIN->value) {
            return [
                'co_managers' => $this->formatCoManagers($this->activeCoManagersQuery->get()),
            ];
        }

        $myProjectIds = Project::query()->where('owner_user_id', $user->id)->pluck('id');
        $comanagerIds = Comanager::query()->whereIn('project_id', $myProjectIds)->pluck('user_id');

        $myCoProjectIds = Comanager::query()->where('user_id', $user->id)->pluck('project_id');
        $ownerIds = Project::query()->whereIn('id', $myCoProjectIds)->pluck('owner_user_id');

        $collaboratorIds = $comanagerIds->merge($ownerIds)
            ->unique()
            ->reject(fn (mixed $id): bool => (int) $id === $user->id)
            ->values()
            ->all();

        if ($collaboratorIds === []) {
            return ['co_managers' => []];
        }

        return [
            'co_managers' => $this->formatCoManagers($this->coManagersByIdsQuery->get($collaboratorIds)),
        ];
    }

    /**
     * @param  Collection<int, User>  $users
     *
     * @return array<int, array{id: int, username: string, name: string, role: string|null}>
     */
    private function formatCoManagers(Collection $users): array {
        return $users
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'role' => $user->role,
            ])
            ->values()
            ->all();
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
                'customization_options' => $task->customization_options !== null
                    ? Arr::map($task->customization_options, fn (array $option): array => Arr::except($option, 'parameters'))
                    : null,
                'tags' => $this->formatTags($task),
                'datasets' => $this->formatDatasets($task),
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, description: string|null, instances_count: int|null}>
     */
    private function formatDatasets(AnnotationTask $task): array {
        return $task->datasets
            ->map(fn (Dataset $dataset): array => [
                'id' => $dataset->id,
                'name' => $dataset->name,
                'description' => $dataset->description,
                'instances_count' => $dataset->instances_count,
            ])
            ->values()
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
