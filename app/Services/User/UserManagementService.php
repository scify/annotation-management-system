<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Queries\GetAnnotatorsByManagerQuery;
use App\Queries\GetAnnotatorsQuery;
use App\Queries\GetUsersByRoleQuery;
use App\Services\Annotation\AnnotatorStatsService;
use App\Services\Project\ProjectService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

readonly class UserManagementService {
    public function __construct(
        private GetUsersByRoleQuery $getUsersByRoleQuery,
        private GetAnnotatorsByManagerQuery $getAnnotatorsByManagerQuery,
        private GetAnnotatorsQuery $getAnnotatorsQuery,
        private ProjectService $projectService,
        private AnnotatorStatsService $annotatorStatsService,
    ) {}

    /**
     * @return array{admins: array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>, all_managers: array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>, my_managers: array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>, all_annotators: array<int, array{id: int, name: string, username: string, status: string, role: string}>, my_annotators: array<int, array{id: int, name: string, username: string, status: string, role: string}>}
     *                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                | array{all_managers: array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>, my_managers: array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>, my_annotators: array<int, array{id: int, name: string, username: string, status: string, role: string}>}
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
        $data = [
            'my_projects' => $this->projectService->getMyProjects($currentUser->id),
            'my_annotators' => $this->getMyAnnotatorsForCreate($currentUser->id),
            'annotation_tasks' => $this->projectService->getAnnotationTasks($currentUser, includeCustomizationOptions: false),
        ];

        if ($currentUser->hasRole(RolesEnum::ADMIN)) {
            $data['all_projects'] = $this->projectService->getAllProjects();
            $data['all_annotators'] = $this->getAllAnnotators();
        }

        return $data;
    }

    /**
     * @return array{
     *     all_managers: array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>
     * }
     */
    public function getDataForCreateNewAnnotator(): array {
        return [
            'all_managers' => $this->mapWithEmail(
                $this->getUsersByRoleQuery->getAllManagers()
            ),
        ];
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
        $allProjects = $this->projectService->getAllProjects();

        return [
            'all_projects' => $allProjects,
            'my_projects' => $this->projectService->getMyProjects($currentUser->id, $allProjects),
            'all_annotators' => $this->getAllAnnotators(),
            'my_annotators' => $this->getMyAnnotatorsForCreate($currentUser->id),
        ];
    }

    /**
     * @return array{
     *     admins: array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>,
     *     all_managers: array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>,
     *     my_managers: array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>,
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
     * @return array{all_managers: array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>, my_managers: array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>, my_annotators: array<int, array{id: int, name: string, username: string, status: string, role: string}>}
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
     * @return array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>
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
