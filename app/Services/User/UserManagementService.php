<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Queries\Annotator\GetAnnotatorIdsByManagerQuery;
use App\Queries\Annotator\GetAnnotatorProjectLinksByProjectQuery;
use App\Queries\Annotator\GetAnnotatorsByManagerQuery;
use App\Queries\Annotator\GetAnnotatorsQuery;
use App\Queries\Annotator\GetManagerIdsByAnnotatorQuery;
use App\Queries\Manager\ConnectManagerToAnnotatorsQuery;
use App\Queries\Manager\GetAnnotationTaskIdsByManagerQuery;
use App\Queries\Manager\GetConnectedProjectIdsByUserQuery;
use App\Queries\Manager\GetDatasetIdsByManagerQuery;
use App\Queries\User\GetUsersByRoleQuery;
use App\Services\Annotation\AnnotatorStatsService;
use App\Services\Project\ProjectReadService;
use App\Services\Settings\AnnotatorPasswordPolicyService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

readonly class UserManagementService {
    public function __construct(
        private GetUsersByRoleQuery $getUsersByRoleQuery,
        private GetAnnotatorsByManagerQuery $getAnnotatorsByManagerQuery,
        private GetAnnotatorsQuery $getAnnotatorsQuery,
        private ProjectReadService $projectReadService,
        private AnnotatorStatsService $annotatorStatsService,
        private GetManagerIdsByAnnotatorQuery $getManagerIdsByAnnotatorQuery,
        private GetAnnotatorIdsByManagerQuery $getAnnotatorIdsByManagerQuery,
        private GetAnnotatorProjectLinksByProjectQuery $annotatorProjectLinksQuery,
        private ConnectManagerToAnnotatorsQuery $connectManagerToAnnotatorsQuery,
        private GetConnectedProjectIdsByUserQuery $getConnectedProjectIdsByUserQuery,
        private GetAnnotationTaskIdsByManagerQuery $getAnnotationTaskIdsByManagerQuery,
        private GetDatasetIdsByManagerQuery $getDatasetIdsByManagerQuery,
        private AnnotatorPasswordPolicyService $policyService,
    ) {}

    /**
     * @return array{admins: array<int, array{id: int, name: string, username: string, email: string|null, status: string, role: string}>, all_managers: array<int, array{id: int, name: string, username: string, email: string|null, status: string, role: string}>, my_managers: array<int, array{id: int, name: string, username: string, email: string|null, status: string, role: string}>, all_annotators: array<int, array{id: int, name: string, username: string, status: string, role: string}>, my_annotators: array<int, array{id: int, name: string, username: string, status: string, role: string}>}
     *                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               | array{all_managers: array<int, array{id: int, name: string, username: string, email: string|null, status: string, role: string}>, my_managers: array<int, array{id: int, name: string, username: string, email: string|null, status: string, role: string}>, my_annotators: array<int, array{id: int, name: string, username: string, status: string, role: string}>}
     *
     * @throws AuthorizationException
     */
    public function getUsersByRole(User $currentUser): array {
        if ($currentUser->hasRole(RolesEnum::ANNOTATOR)) {
            throw new AuthorizationException();
        }

        if ($currentUser->hasRole(RolesEnum::ADMIN)) {
            return $this->getForAdmin($currentUser->id);
        }

        return $this->getForManager($currentUser->id);
    }

    /**
     * @return array{
     *     my_projects: array<int, array<string, mixed>>,
     *     my_annotators: array<int, array<string, mixed>>,
     *     annotation_tasks: array<int, array<string, mixed>>,
     *     all_projects?: array<int, array<string, mixed>>,
     *     all_annotators?: array<int, array<string, mixed>>
     * }
     */
    public function getDataForCreateNewManager(User $currentUser): array {
        $myProjects = $this->projectReadService->getMyProjects($currentUser->id);
        $this->augmentProjectsWithAnnotatorIds($myProjects);

        $data = [
            'my_projects' => $myProjects,
            'my_annotators' => $this->getMyAnnotatorsForCreate($currentUser->id),
            'annotation_tasks' => $this->projectReadService->getAnnotationTasks($currentUser, includeCustomizationOptions: false),
        ];

        if ($currentUser->hasRole(RolesEnum::ADMIN)) {
            $allProjects = $this->projectReadService->getAllProjects();
            $this->augmentProjectsWithAnnotatorIds($allProjects);
            $data['all_projects'] = $allProjects;
            $data['all_annotators'] = $this->getAllAnnotators();
        }

        return $data;
    }

    /**
     * @param  array<int, int>  $annotatorIds
     */
    public function connectAnnotatorsToManager(int $managerId, array $annotatorIds): void {
        $this->connectManagerToAnnotatorsQuery->bulkConnect($managerId, $annotatorIds);
    }

    /**
     * @return array{
     *     manager: array{id: int, username: string},
     *     my_annotators: array<int, array<string, mixed>>,
     *     annotator_ids: array<int, int>,
     *     all_annotators?: array<int, array<string, mixed>>
     * }
     */
    public function getDataForConnectAnnotators(User $targetManager, User $currentUser): array {
        $data = [
            'manager' => ['id' => $targetManager->id, 'username' => $targetManager->username],
            'my_annotators' => $this->getMyAnnotatorsForCreate($currentUser->id),
            'annotator_ids' => $this->getAnnotatorIdsByManagerQuery->get($targetManager->id),
        ];

        if ($currentUser->hasRole(RolesEnum::ADMIN)) {
            $data['all_annotators'] = $this->getAllAnnotators();
        }

        return $data;
    }

    /**
     * @return array{
     *     all_managers: array<int, array{id: int, name: string, username: string, email: string|null, status: string, role: string}>,
     *     password_policy: array{min_length: int, composition_mode: string, mixed_case_required: bool}
     * }
     */
    public function getDataForCreateNewAnnotator(): array {
        $policy = $this->policyService->getPolicy();

        return [
            'all_managers' => $this->mapWithEmail(
                $this->getUsersByRoleQuery->getAllManagers()
            ),
            'password_policy' => [
                'min_length' => $policy->min_length,
                'composition_mode' => $policy->composition_mode->value,
                'mixed_case_required' => $policy->mixed_case_required,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataForEditAnnotator(User $targetUser): array {
        return array_merge(
            $this->getDataForCreateNewAnnotator(),
            ['user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'username' => $targetUser->username,
                'status' => $targetUser->status->value,
                'manager_ids' => $this->getManagerIdsByAnnotatorQuery->get($targetUser->id),
            ]],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataForEditAdmin(User $currentUser, User $targetUser): array {
        return array_merge(
            $this->getDataForCreateNewAdmin($currentUser),
            ['user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'username' => $targetUser->username,
                'email' => $targetUser->email,
                'status' => $targetUser->status->value,
                'project_ids' => $this->getConnectedProjectIdsByUserQuery->get($targetUser->id),
                'annotator_ids' => $this->getAnnotatorIdsByManagerQuery->get($targetUser->id),
            ]],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getDataForEditManager(User $currentUser, User $targetUser): array {
        return array_merge(
            $this->getDataForCreateNewManager($currentUser),
            ['user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'username' => $targetUser->username,
                'email' => $targetUser->email,
                'status' => $targetUser->status->value,
                'project_ids' => $this->getConnectedProjectIdsByUserQuery->get($targetUser->id),
                'annotator_ids' => $this->getAnnotatorIdsByManagerQuery->get($targetUser->id),
                'annotation_task_ids' => $this->getAnnotationTaskIdsByManagerQuery->get($targetUser->id),
                'dataset_ids' => $this->getDatasetIdsByManagerQuery->get($targetUser->id),
            ]],
        );
    }

    /**
     * @return array{
     *     all_projects: array<int, array<string, mixed>>,
     *     my_projects: array<int, array<string, mixed>>,
     *     all_annotators: array<int, array<string, mixed>>,
     *     my_annotators: array<int, array<string, mixed>>
     * }
     */
    public function getDataForCreateNewAdmin(User $currentUser): array {
        $allProjects = $this->projectReadService->getAllProjects();
        $this->augmentProjectsWithAnnotatorIds($allProjects);

        return [
            'all_projects' => $allProjects,
            'my_projects' => $this->projectReadService->getMyProjects($currentUser->id, $allProjects),
            'all_annotators' => $this->getAllAnnotators(),
            'my_annotators' => $this->getMyAnnotatorsForCreate($currentUser->id),
        ];
    }

    /**
     * @return array{
     *     admins: array<int, array{id: int, name: string, username: string, email: string|null, status: string, role: string}>,
     *     all_managers: array<int, array{id: int, name: string, username: string, email: string|null, status: string, role: string}>,
     *     my_managers: array<int, array{id: int, name: string, username: string, email: string|null, status: string, role: string}>,
     *     all_annotators: array<int, array{id: int, name: string, username: string, status: string, role: string}>,
     *     my_annotators: array<int, array{id: int, name: string, username: string, status: string, role: string}>
     * }
     */
    private function getForAdmin(int $currentUserId): array {
        $all = $this->getUsersByRoleQuery->get();

        return [
            'admins' => $this->mapWithEmail(
                $all->filter(fn (User $u): bool => $u->role === RolesEnum::ADMIN->value)
            ),
            'all_managers' => $this->mapWithEmail(
                $all->filter(fn (User $u): bool => $u->role === RolesEnum::ANNOTATION_MANAGER->value)
            ),
            'my_managers' => $this->mapWithEmail(
                $this->getUsersByRoleQuery->getMyManagers($currentUserId)
            ),
            'all_annotators' => $this->mapWithoutEmail(
                $all->filter(fn (User $u): bool => $u->role === RolesEnum::ANNOTATOR->value)
            ),
            'my_annotators' => $this->mapMyAnnotators(
                $this->getAnnotatorsByManagerQuery->get($currentUserId)
            ),
        ];
    }

    /**
     * @return array{all_managers: array<int, array{id: int, name: string, username: string, email: string|null, status: string, role: string}>, my_managers: array<int, array{id: int, name: string, username: string, email: string|null, status: string, role: string}>, my_annotators: array<int, array{id: int, name: string, username: string, status: string, role: string}>}
     */
    private function getForManager(int $currentUserId): array {
        return [
            'all_managers' => $this->mapWithEmail(
                $this->getUsersByRoleQuery->getAllManagers()
            ),
            'my_managers' => $this->mapWithEmail(
                $this->getUsersByRoleQuery->getMyManagers($currentUserId)
            ),
            'my_annotators' => $this->mapMyAnnotators(
                $this->getAnnotatorsByManagerQuery->get($currentUserId)
            ),
        ];
    }

    /**
     * @param  Collection<int, User>  $users
     *
     * @return array<int, array{id: int, name: string, username: string, email: string|null, status: string, role: string}>
     */
    private function mapWithEmail(Collection $users): array {
        return $users->map(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'status' => $user->status->value,
            'role' => (string) $user->role,
        ])->values()->all();
    }

    /**
     * @param  Collection<int, User>  $users
     *
     * @return array<int, array{id: int, name: string, username: string, status: string, role: string}>
     */
    private function mapWithoutEmail(Collection $users): array {
        return $users->map(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'status' => $user->status->value,
            'role' => (string) $user->role,
        ])->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getAllAnnotators(): array {
        return $this->annotatorStatsService->buildAnnotatorsData(
            $this->getAnnotatorsQuery->getAll(),
            includeSubprojects: false,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getMyAnnotatorsForCreate(int $managerId): array {
        return $this->annotatorStatsService->buildAnnotatorsData(
            $this->getAnnotatorsByManagerQuery->get($managerId),
            includeSubprojects: false,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $projects
     */
    private function augmentProjectsWithAnnotatorIds(array &$projects): void {
        if ($projects === []) {
            return;
        }

        /** @var array<int, int> $projectIds */
        $projectIds = array_column($projects, 'id');

        $rows = $this->annotatorProjectLinksQuery->getByProjectIds($projectIds);

        /** @var array<int, array<int, int>> $annotatorIdsByProject */
        $annotatorIdsByProject = [];
        foreach ($rows as $row) {
            $annotatorIdsByProject[$row->project_id][] = $row->user_id;
        }

        foreach ($projects as &$project) {
            $id = $project['id'];
            $project['annotators'] = is_int($id) ? ($annotatorIdsByProject[$id] ?? []) : [];
        }
    }

    /**
     * Like mapWithoutEmail but hardcodes the role — used when users are fetched without
     * a roles JOIN (e.g. via GetAnnotatorsByManagerQuery which doesn't eager-load roles).
     *
     * @param  Collection<int, User>  $users
     *
     * @return array<int, array{id: int, name: string, username: string, status: string, role: string}>
     */
    private function mapMyAnnotators(Collection $users): array {
        return $users->map(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'status' => $user->status->value,
            'role' => RolesEnum::ANNOTATOR->value,
        ])->values()->all();
    }
}
