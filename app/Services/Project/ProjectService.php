<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\AnnotationTask;
use App\Models\AnnotatorOfProject;
use App\Models\Dataset;
use App\Models\InstanceShuffleMapper;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\TaskTag;
use App\Models\User;
use App\Queries\GetAnnotationTasksQuery;
use App\Queries\GetAnnotatorIdsByProjectsQuery;
use App\Queries\GetCoManagersQuery;
use App\Queries\GetProjectIdsManagedByUserQuery;
use App\Queries\GetProjectsByIdsQuery;
use App\Queries\GetProjectsManagedByUserQuery;
use App\Queries\GetProjectsQuery;
use App\Queries\GetSubProjectIdsQuery;
use App\Services\Annotation\AnnotatorService;
use App\Services\Dataset\DatasetService;
use App\Services\SubProject\SubProjectService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class ProjectService {
    public function __construct(
        private AnnotatorService $annotatorService,
        private DatasetService $datasetService,
        private SubProjectService $subProjectService,
        private GetCoManagersQuery $coManagersQuery,
        private GetAnnotationTasksQuery $getAnnotationTasksQuery,
        private GetAnnotatorIdsByProjectsQuery $annotatorIdsByProjectsQuery,
        private GetProjectIdsManagedByUserQuery $projectIdsByManagerQuery,
        private GetProjectsByIdsQuery $projectsByIdsQuery,
        private GetProjectsQuery $projectsQuery,
        private GetSubProjectIdsQuery $subProjectIdsQuery,
        private GetProjectsManagedByUserQuery $userProjectsQuery,
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
                'status' => ProjectStatusEnum::PENDING,
                'is_instance_shuffled' => $data['is_instance_shuffled'],
                'annotation_task_configuration' => $data['annotation_task_configuration'] ?? null,
                'restricted_visibility' => $data['restricted_visibility'],
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'deadline_at' => $data['deadline_at'] ?? null,
            ]);

            ProjectManager::query()->create([
                'project_id' => $project->id,
                'user_id' => $owner->id,
                'accepted' => true,
            ]);

            foreach ($coManagerIds as $managerId) {
                ProjectManager::query()->firstOrCreate(
                    ['project_id' => $project->id, 'user_id' => $managerId],
                    ['accepted' => true],
                );
            }

            if ($project->is_instance_shuffled) {
                $shuffled = $this->datasetService->generateShuffledIndexArray($project->dataset_id);
                $now = now();
                $rows = [];
                foreach ($shuffled as $newIndex => $oldIndex) {
                    $rows[] = [
                        'project_id' => $project->id,
                        'new_index' => $newIndex + 1,
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
     * Assign all annotators of a project to a (co-)manager.
     * Called when a co-manager accepts a project invitation.
     *
     * TODO: wire this call into the invitation acceptance handler once implemented.
     */
    public function assignAnnotatorsToManagers(int $projectId, int $managerId): void {
        $annotatorIds = AnnotatorOfProject::query()
            ->where('project_id', $projectId)
            ->pluck('user_id')
            ->all();

        if ($annotatorIds === []) {
            return;
        }

        $now = now();
        $rows = [];
        foreach ($annotatorIds as $annotatorId) {
            $rows[] = [
                'manager_id' => $managerId,
                'annotator_id' => $annotatorId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('annotator_of_managers')->insertOrIgnore($rows);
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataForProjectsPage(User $user, bool $withFilters = true): array {
        $roleName = $user->getRoleNames()->first();

        if ($roleName === RolesEnum::ADMIN->value) {
            $allProjects = $this->getAllProjects();
            $myProjects = $this->getMyProjects($user->id, $allProjects);

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

        $myProjects = $this->getMyProjects($user->id);

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
     * @return array<string, mixed>
     */
    public function getDataForShowProject(int $id): array {
        $project = $this->projectsByIdsQuery->get([$id])->firstOrFail();
        $subprojectsData = $this->subProjectService->getSubProjectsData($project->subProjects);

        $annotatorPivotRows = AnnotatorOfProject::query()->where('project_id', $project->id)->get();

        /** @var array<int, int> $annotatorIds */
        $annotatorIds = $annotatorPivotRows->pluck('user_id')->all();

        /** @var array<int, bool> $canFlagByAnnotatorId */
        $canFlagByAnnotatorId = $annotatorPivotRows
            ->mapWithKeys(fn (AnnotatorOfProject $row): array => [$row->user_id => $row->can_flag])
            ->all();

        /** @var \Illuminate\Support\Collection<int, int> $subProjectIds */
        $subProjectIds = $project->subProjects->pluck('id');
        $progressBySubProject = $this->subProjectService->getProgress($subProjectIds->all());

        $annotatorsData = $this->annotatorService->getProjectAnnotatorsData($annotatorIds, $subProjectIds, $progressBySubProject);

        $annotatorsData = array_map(
            fn (array $annotator): array => [
                ...$annotator,
                'can_flag' => ! is_int($annotator['id']) || (($canFlagByAnnotatorId[$annotator['id']] ?? true)),
            ],
            $annotatorsData,
        );

        return [
            'project_data' => $this->buildProjectData($project, $subprojectsData),
            'subprojects_data' => $subprojectsData,
            'annotators_data' => $annotatorsData,
            'comanagers_data' => $this->buildCoManagersData($project),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllInProgressProjects(): array {
        $data = $this->projectsQuery->get(ProjectStatusEnum::IN_PROGRESS)
            ->map(fn (Project $project): array => array_merge(
                $project->toArray(),
                ['is_delayed_to_start' => $project->isDelayedToStart(), 'is_delayed_to_end' => $project->isDelayedToEnd()]
            ))
            ->values()
            ->all();

        $progressBySubProject = $this->subProjectService->getProgress($this->extractSubProjectIds($data));
        $this->augmentProjectData($data, $progressBySubProject);

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
            $data = $this->userProjectsQuery->get($userId, ProjectStatusEnum::IN_PROGRESS)
                ->map(fn (Project $project): array => array_merge(
                    $project->toArray(),
                    ['is_delayed_to_start' => $project->isDelayedToStart(), 'is_delayed_to_end' => $project->isDelayedToEnd()]
                ))
                ->values()
                ->all();

            $progressBySubProject = $this->subProjectService->getProgress($this->extractSubProjectIds($data));
            $this->augmentProjectData($data, $progressBySubProject);

            return $data;
        }

        $myProjectIds = $this->projectIdsByManagerQuery->get($userId);

        return array_values(array_filter(
            $allProjects,
            fn (array $project): bool => is_int($project['id']) && in_array($project['id'], $myProjectIds, true),
        ));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllProjects(): array {
        $data = $this->projectsQuery->get()
            ->map(fn (Project $project): array => array_merge(
                $project->toArray(),
                ['is_delayed_to_start' => $project->isDelayedToStart(), 'is_delayed_to_end' => $project->isDelayedToEnd()]
            ))
            ->values()
            ->all();

        $progressBySubProject = $this->subProjectService->getProgress($this->extractSubProjectIds($data));
        $this->augmentProjectData($data, $progressBySubProject);

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
    public function getMyProjects(int $userId, ?array $allProjects = null): array {
        if ($allProjects === null) {
            $data = $this->userProjectsQuery->get($userId)
                ->map(fn (Project $project): array => array_merge(
                    $project->toArray(),
                    ['is_delayed_to_start' => $project->isDelayedToStart(), 'is_delayed_to_end' => $project->isDelayedToEnd()]
                ))
                ->values()
                ->all();

            $progressBySubProject = $this->subProjectService->getProgress($this->extractSubProjectIds($data));
            $this->augmentProjectData($data, $progressBySubProject);

            return $data;
        }

        $myProjectIds = $this->projectIdsByManagerQuery->get($userId);

        return array_values(array_filter(
            $allProjects,
            fn (array $project): bool => is_int($project['id']) && in_array($project['id'], $myProjectIds, true),
        ));
    }

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
     * @return array<int, array{id: int, username: string, email: string, status: string, owner: bool, accepted: bool}>
     */
    private function buildCoManagersData(Project $project): array {
        return $project->projectManagers
            ->map(fn (ProjectManager $pm): array => [
                'id' => $pm->user->id,
                'username' => $pm->user->username,
                'email' => $pm->user->email,
                'status' => $pm->user->status->value,
                'owner' => $pm->user_id === $project->owner_user_id,
                'accepted' => (bool) $pm->accepted,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{progress: float, ...}>  $subprojectsData
     *
     * @return array<string, mixed>
     */
    private function buildProjectData(Project $project, array $subprojectsData): array {
        $subProjectCount = count($subprojectsData);
        $progress = $subProjectCount > 0
            ? (float) (array_sum(array_column($subprojectsData, 'progress')) / $subProjectCount)
            : 0.0;

        return [
            'id' => $project->id,
            'name' => $project->name,
            'annotation_task_title' => $project->annotationTask->title,
            'dataset_name' => $project->dataset->name,
            'project_progress' => $progress,
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

        /** @var array<int, int> $allSubProjectIds */
        $allSubProjectIds = $this->subProjectIdsQuery->getAll()->all();
        $progressBySubProject = $this->subProjectService->getProgress($allSubProjectIds);

        /** @var \Illuminate\Support\Collection<int, int> $activeSubProjectIds */
        $activeSubProjectIds = collect($allSubProjectIds);

        $myProjectIds = $this->userProjectsQuery->get($user->id, ProjectStatusEnum::IN_PROGRESS)->pluck('id')->all();

        if ($roleName === RolesEnum::ADMIN->value) {
            $allAnnotators = $this->annotatorService->getAllAnnotators($progressBySubProject, $activeSubProjectIds);

            return [
                'all_annotators' => $allAnnotators,
                'my_annotators' => $this->resolveMyAnnotators($myProjectIds, $allAnnotators),
            ];
        }

        return [
            'my_annotators' => $this->resolveMyAnnotators($myProjectIds, progressBySubProject: $progressBySubProject, activeSubProjectIds: $activeSubProjectIds),
        ];
    }

    /**
     * @param  array<int, mixed>  $projectIds
     * @param  array<int, array<string, mixed>>|null  $allAnnotators
     * @param  array<int, array{progress: float, assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>}>  $progressBySubProject
     * @param  \Illuminate\Support\Collection<int, int>|null  $activeSubProjectIds
     *
     * @return array<int, array<string, mixed>>
     */
    private function resolveMyAnnotators(
        array $projectIds,
        ?array $allAnnotators = null,
        array $progressBySubProject = [],
        ?\Illuminate\Support\Collection $activeSubProjectIds = null,
    ): array {
        if ($projectIds === []) {
            return [];
        }

        $annotatorIds = $this->annotatorIdsByProjectsQuery->get($projectIds);

        if ($annotatorIds === []) {
            return [];
        }

        if ($allAnnotators !== null) {
            return array_values(array_filter(
                $allAnnotators,
                fn (array $annotator): bool => is_int($annotator['id']) && in_array($annotator['id'], $annotatorIds, true),
            ));
        }

        return $this->annotatorService->getAnnotatorsByIds($annotatorIds, $progressBySubProject, $activeSubProjectIds);
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
                'co_managers' => $this->formatCoManagers($this->coManagersQuery->get()),
            ];
        }

        $myProjectIds = ProjectManager::query()->where('user_id', $user->id)->pluck('project_id');
        $collaboratorIds = ProjectManager::query()
            ->whereIn('project_id', $myProjectIds)
            ->pluck('user_id')
            ->unique()
            ->reject(fn (mixed $id): bool => is_numeric($id) && (int) $id === $user->id)
            ->values()
            ->all();

        if ($collaboratorIds === []) {
            return ['co_managers' => []];
        }

        return [
            'co_managers' => $this->formatCoManagers($this->coManagersQuery->get($collaboratorIds)),
        ];
    }

    /**
     * @param  Collection<int, User>  $users
     *
     * @return array<int, array{id: int, username: string, name: string, role: string|null, status: string}>
     */
    private function formatCoManagers(Collection $users): array {
        return $users
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'role' => $user->role,
                'status' => $user->status->value,
            ])
            ->values()
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
            /** @var int|string $id */
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
            /** @var int|string $id */
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
     *
     * @return array<int, int>
     */
    private function extractSubProjectIds(array $data): array {
        $ids = [];
        foreach ($data as $project) {
            $subProjects = $project['sub_projects'] ?? [];
            if (is_array($subProjects)) {
                array_push($ids, ...array_column($subProjects, 'id'));
            }
        }

        /** @var array<int, int> $unique */
        $unique = array_unique($ids);

        return $unique;
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @param  array<int, array{progress: float, assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>}>  $progressBySubProject
     */
    private function augmentProjectData(array &$data, array $progressBySubProject = []): void {
        $this->augmentProjectsWithAnnotationTasks($data);
        $this->augmentProjectsWithNotifications($data);
        $this->augmentProjectsWithManagers($data);
        $this->augmentProjectsWithProgress($data, $progressBySubProject);
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    private function augmentProjectsWithManagers(array &$data): void {
        foreach ($data as &$project) {
            /** @var int|string $ownerUserId */
            $ownerUserId = $project['owner_user_id'];
            $ownerId = (int) $ownerUserId;
            $owner = $project['owner'];
            $project['owner_name'] = is_array($owner) ? ($owner['username'] ?? null) : null;
            /** @var array<int, array<string, mixed>> $projectManagers */
            $projectManagers = $project['project_managers'] ?? [];
            $project['co_managers'] = array_values(array_filter(
                array_map(
                    function (array $relation) use ($ownerId): ?array {
                        /** @var array{id: int|string, username: string}|null $user */
                        $user = $relation['user'] ?? null;

                        return is_array($user) && (int) $user['id'] !== $ownerId
                            ? ['id' => $user['id'], 'username' => $user['username']]
                            : null;
                    },
                    $projectManagers,
                ),
            ));
            unset($project['owner'], $project['project_managers']);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     * @param  array<int, array{progress: float, assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>}>  $progressBySubProject
     */
    private function augmentProjectsWithProgress(array &$data, array $progressBySubProject = []): void {
        $subProjectIdsByIndex = [];

        foreach ($data as $i => $project) {
            $subProjects = $project['sub_projects'] ?? [];
            $ids = is_array($subProjects) ? array_column($subProjects, 'id') : [];
            $subProjectIdsByIndex[$i] = $ids;
        }

        foreach ($data as $i => &$project) {
            $ids = $subProjectIdsByIndex[$i];
            $project['subprojects_count'] = count($ids);
            unset($project['sub_projects']);

            if ($ids === []) {
                $project['project_progress'] = 0.0;

                continue;
            }

            $total = 0.0;
            foreach ($ids as $spId) {
                $total += $progressBySubProject[$spId]['progress'] ?? 0.0;
            }

            $project['project_progress'] = $total / count($ids);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    private function augmentProjectsWithNotifications(array &$data): void {
        /** @var array<int, int> $projectIds */
        $projectIds = array_column($data, 'id');
        $notificationCounts = $this->getNotificationCounts($projectIds);

        foreach ($data as &$project) {
            /** @var int $projectId */
            $projectId = $project['id'];
            $project['notifications_count'] = $notificationCounts[$projectId] ?? 0;
        }
    }

    /**
     * TODO: implement once notifications are available.
     *
     * @param  array<int, int>  $projectIds
     *
     * @return array<int, int>
     */
    private function getNotificationCounts(array $projectIds): array {
        return array_fill_keys($projectIds, 0);
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    private function augmentProjectsWithAnnotationTasks(array &$data): void {
        foreach ($data as &$project) {
            $task = $project['annotation_task'];
            $dataset = $project['dataset'];
            $project['annotation_task_title'] = is_array($task) ? ($task['title'] ?? null) : null;
            $project['dataset_name'] = is_array($dataset) ? ($dataset['name'] ?? null) : null;
            unset($project['annotation_task'], $project['dataset']);
        }
    }
}
