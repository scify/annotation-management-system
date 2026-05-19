<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\AnnotationTask;
use App\Models\AnnotatorOfManager;
use App\Models\Dataset;
use App\Models\InstanceShuffleMapper;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\TaskTag;
use App\Models\User;
use App\Queries\GetActiveCoManagersQuery;
use App\Queries\GetAnnotationTasksQuery;
use App\Queries\GetAnnotatorIdsByProjectsQuery;
use App\Queries\GetCoManagersByIdsQuery;
use App\Queries\GetInProgressProjectsQuery;
use App\Queries\GetProjectIdsByManagerQuery;
use App\Queries\GetUserInProgressProjectsQuery;
use App\Services\Annotator\AnnotatorService;
use App\Services\Dataset\DatasetService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class ProjectService {
    public function __construct(
        private AnnotatorService $annotatorService,
        private DatasetService $datasetService,
        private GetActiveCoManagersQuery $activeCoManagersQuery,
        private GetAnnotationTasksQuery $getAnnotationTasksQuery,
        private GetAnnotatorIdsByProjectsQuery $annotatorIdsByProjectsQuery,
        private GetCoManagersByIdsQuery $coManagersByIdsQuery,
        private GetInProgressProjectsQuery $inProgressProjectsQuery,
        private GetProjectIdsByManagerQuery $projectIdsByManagerQuery,
        private GetUserInProgressProjectsQuery $userInProgressProjectsQuery,
    ) {}

    /**
     * Creates a project with its manager assignments and annotator snapshot.
     *
     * @param  array<string, mixed>  $data  Validated data from ProjectStoreRequest
     *
     * @throws Throwable
     */
    public function storeProject(User $owner, array $data): Project {
        return DB::transaction(function () use ($owner, $data): Project {
            /** @var array<int, int> $annotatorIds */
            $annotatorIds = $data['annotator_ids'];
            /** @var array<int, int> $coManagerIds */
            $coManagerIds = $data['co_manager_ids'] ?? [];

            $project = Project::query()->create([
                'name' => $data['name'],
                'owner_user_id' => $owner->id,
                'annotation_task_id' => $data['annotation_task_id'],
                'dataset_id' => $data['dataset_id'],
                'status' => ProjectStatusEnum::IN_PROGRESS,
                'is_instance_shuffled' => $data['is_instance_shuffled'],
                'annotation_task_configuration' => $data['annotation_task_configuration'] ?? null,
                'restricted_visibility' => $data['restricted_visibility'],
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'deadline_at' => $data['deadline_at'] ?? null,
            ]);

            ProjectManager::query()->create([
                'project_id' => $project->id,
                'user_id' => $owner->id,
            ]);

            foreach ($coManagerIds as $managerId) {
                ProjectManager::query()->firstOrCreate([
                    'project_id' => $project->id,
                    'user_id' => $managerId,
                ]);
            }

            if ($project->is_instance_shuffled) {
                $shuffled = $this->datasetService->generateShuffledIndexArray($project->dataset_id);
                $now = now();
                $rows = [];
                foreach ($shuffled as $newIndex => $oldIndex) {
                    $rows[] = [
                        'project_id' => $project->id,
                        'new_index' => $newIndex,
                        'old_index' => $oldIndex,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                InstanceShuffleMapper::query()->insert($rows);
            }

            $project->annotators()->sync($annotatorIds);

            return $project;
        });
    }

    /**
     * Snapshots the annotators of the given managers into annotator_of_project.
     * Called during project creation after managers are assigned.
     *
     * @param  array<int, int>  $managerIds
     */
    public function assignAnnotatorsFromManagers(Project $project, array $managerIds): void {
        if ($managerIds === []) {
            return;
        }

        $annotatorIds = AnnotatorOfManager::query()
            ->whereIn('manager_id', $managerIds)
            ->distinct()
            ->pluck('annotator_id')
            ->all();

        $project->annotators()->sync($annotatorIds);
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataForProjectsPage(User $user, bool $withFilters = true): array {
        $roleName = $user->getRoleNames()->first();

        // TODO Aris: this if should not happen
        // because this route should be "gated" and no annotators should be able to access it.
        if ($roleName === RolesEnum::ANNOTATOR->value) {
            return [];
        }

        if ($roleName === RolesEnum::ADMIN->value) {
            $allProjects = $this->getAllInProgressProjects();
            $myProjects = $this->getMyInProgressProjects($user->id, $allProjects);

            $data = [
                'all_projects' => $allProjects,
                'my_projects' => $myProjects,
            ];

            if ($withFilters) {
                $data['all_data_filter'] = [
                    'tasks_filter' => $this->extractTaskFilters($allProjects),
                    'datasets_filter' => $this->extractDatasetFilters($allProjects),
                ];
                $data['my_data_filter'] = [
                    'tasks_filter' => $this->extractTaskFilters($myProjects),
                    'datasets_filter' => $this->extractDatasetFilters($myProjects),
                ];
            }

            return $data;
        }

        $myProjects = $this->getMyInProgressProjects($user->id);

        $data = ['my_projects' => $myProjects];

        if ($withFilters) {
            $data['my_data_filter'] = [
                'tasks_filter' => $this->extractTaskFilters($myProjects),
                'datasets_filter' => $this->extractDatasetFilters($myProjects),
            ];
        }

        return $data;
    }

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
     * @return array<int, array<string, mixed>>
     */
    public function getAllInProgressProjects(): array {
        $data = $this->inProgressProjectsQuery->get()
            ->map(fn (Project $project): array => array_merge(
                $project->toArray(),
                ['is_delayed_to_start' => $project->isDelayedToStart(), 'is_delayed_to_end' => $project->isDelayedToEnd()]
            ))
            ->values()
            ->all();

        $this->augmentProjectData($data);

        return $data;
    }

    /**
     * When $allProjects is provided (admin case), filters from the already-loaded set
     * instead of issuing a second query.
     *
     * @param  array<int, array<string, mixed>>|null  $allProjects
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMyInProgressProjects(int $userId, ?array $allProjects = null): array {
        if ($allProjects === null) {
            $data = $this->userInProgressProjectsQuery->get($userId)
                ->map(fn (Project $project): array => array_merge(
                    $project->toArray(),
                    ['is_delayed_to_start' => $project->isDelayedToStart(), 'is_delayed_to_end' => $project->isDelayedToEnd()]
                ))
                ->values()
                ->all();

            $this->augmentProjectData($data);

            return $data;
        }

        $myProjectIds = $this->projectIdsByManagerQuery->get($userId);

        return array_values(array_filter(
            $allProjects,
            fn (array $project): bool => in_array((int) $project['id'], $myProjectIds, true),
        ));
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

        $myProjectIds = ProjectManager::query()->where('user_id', $user->id)->pluck('project_id');
        $collaboratorIds = ProjectManager::query()
            ->whereIn('project_id', $myProjectIds)
            ->pluck('user_id')
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

    /**
     * @param  array<int, array<string, mixed>>  $projects
     *
     * @return array<int, array{id: mixed, title: mixed}>
     */
    private function extractTaskFilters(array $projects): array {
        $seen = [];
        $tasks = [];
        foreach ($projects as $project) {
            $id = $project['annotation_task_id'];
            if (! isset($seen[$id])) {
                $seen[$id] = true;
                $tasks[] = ['id' => $id, 'title' => $project['annotation_task_title']];
            }
        }

        return $tasks;
    }

    /**
     * @param  array<int, array<string, mixed>>  $projects
     *
     * @return array<int, array{id: mixed, name: mixed}>
     */
    private function extractDatasetFilters(array $projects): array {
        $seen = [];
        $datasets = [];
        foreach ($projects as $project) {
            $id = $project['dataset_id'];
            if (! isset($seen[$id])) {
                $seen[$id] = true;
                $datasets[] = ['id' => $id, 'name' => $project['dataset_name']];
            }
        }

        return $datasets;
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    private function augmentProjectData(array &$data): void {
        $this->augmentProjectsWithAnnotationTasks($data);
        $this->augmentProjectsWithNotifications($data);
        $this->augmentProjectsWithManagers($data);
        $this->augmentProjectsWithProgress($data);
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    private function augmentProjectsWithManagers(array &$data): void {
        foreach ($data as &$project) {
            $ownerId = (int) $project['owner_user_id'];
            $project['owner_name'] = $project['owner']['username'] ?? null;
            $project['co_managers'] = array_values(array_filter(
                array_map(
                    fn (array $relation): ?array => isset($relation['user']) && (int) $relation['user']['id'] !== $ownerId
                        ? ['id' => $relation['user']['id'], 'username' => $relation['user']['username']]
                        : null,
                    $project['project_managers'] ?? [],
                ),
            ));
            unset($project['owner'], $project['project_managers']);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    private function augmentProjectsWithProgress(array &$data): void {
        foreach ($data as &$project) {
            $project['project_progress'] = 0.5;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    private function augmentProjectsWithNotifications(array &$data): void {
        foreach ($data as &$project) {
            $project['notifications_count'] = 0;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    private function augmentProjectsWithAnnotationTasks(array &$data): void {
        foreach ($data as &$project) {
            $project['annotation_task_title'] = $project['annotation_task']['title'] ?? null;
            $project['dataset_name'] = $project['dataset']['name'] ?? null;
            unset($project['annotation_task'], $project['dataset']);
        }
    }
}
