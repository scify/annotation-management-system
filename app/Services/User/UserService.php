<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Exceptions\UserCreationException;
use App\Models\User;
use App\Notifications\UserWelcomeNotification;
use App\Queries\Annotator\ConnectAnnotatorToManagersQuery;
use App\Queries\Annotator\GetWorkloadsByAnnotatorsQuery;
use App\Queries\Annotator\SyncManagersForAnnotatorQuery;
use App\Queries\Dataset\GetDatasetIdsByAnnotationTaskIdsQuery;
use App\Queries\Manager\ConnectManagerToAnnotationTasksQuery;
use App\Queries\Manager\ConnectManagerToAnnotatorsQuery;
use App\Queries\Manager\ConnectManagerToDatasetsQuery;
use App\Queries\Manager\ConnectManagerToProjectsQuery;
use App\Queries\Manager\SyncAnnotationTasksForManagerQuery;
use App\Queries\Manager\SyncAnnotatorsForManagerQuery;
use App\Queries\Manager\SyncDatasetsForManagerQuery;
use App\Queries\Manager\SyncProjectsForManagerQuery;
use App\Queries\User\CreateAdminQuery;
use App\Queries\User\CreateAnnotatorQuery;
use App\Queries\User\CreateManagerQuery;
use App\Queries\User\FindNextDeletionIndexQuery;
use App\Queries\User\FindUserByEmailQuery;
use App\Queries\User\FindUserByNameQuery;
use App\Queries\User\FindUserByUsernameQuery;
use App\Queries\User\GetUsersQuery;
use Illuminate\Support\Collection;
use InvalidArgumentException;

readonly class UserService {
    public function __construct(
        private FindNextDeletionIndexQuery $findNextDeletionIndexQuery,
        private FindUserByEmailQuery $findUserByEmailQuery,
        private FindUserByNameQuery $findUserByNameQuery,
        private FindUserByUsernameQuery $findUserByUsernameQuery,
        private GetWorkloadsByAnnotatorsQuery $getUserWorkloadsQuery,
        private GetUsersQuery $getUsersQuery,
        private CreateAnnotatorQuery $createAnnotatorQuery,
        private ConnectAnnotatorToManagersQuery $connectAnnotatorToManagersQuery,
        private CreateAdminQuery $createAdminQuery,
        private ConnectManagerToProjectsQuery $connectManagerToProjectsQuery,
        private ConnectManagerToAnnotatorsQuery $connectManagerToAnnotatorsQuery,
        private CreateManagerQuery $createManagerQuery,
        private ConnectManagerToAnnotationTasksQuery $connectManagerToAnnotationTasksQuery,
        private ConnectManagerToDatasetsQuery $connectManagerToDatasetsQuery,
        private SyncManagersForAnnotatorQuery $syncManagersForAnnotatorQuery,
        private SyncAnnotatorsForManagerQuery $syncAnnotatorsForManagerQuery,
        private SyncProjectsForManagerQuery $syncProjectsForManagerQuery,
        private SyncAnnotationTasksForManagerQuery $syncAnnotationTasksForManagerQuery,
        private SyncDatasetsForManagerQuery $syncDatasetsForManagerQuery,
        private GetDatasetIdsByAnnotationTaskIdsQuery $getDatasetIdsByAnnotationTaskIdsQuery,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws UserCreationException
     */
    public function create(array $data, ?User $creator = null): User {
        $roleValue = $data['role'] ?? null;
        $role = is_string($roleValue) ? RolesEnum::tryFrom($roleValue) : null;

        if ($role === RolesEnum::ANNOTATOR) {
            /** @var array{name: string, username: string, password: string, password_confirmation: string, manager_ids: array<int, int>, role: string} $data */
            return $this->createAnnotator($data);
        }

        if ($role === RolesEnum::ADMIN) {
            /** @var array{name: string, username: string, email: string, password: string, password_confirmation: string, project_ids: array<int, int>, annotator_ids: array<int, int>, role: string} $data */
            $user = $this->createAdmin($data);
        } elseif ($role === RolesEnum::ANNOTATION_MANAGER) {
            /** @var array{name: string, username: string, email: string, password: string, password_confirmation: string, project_ids: array<int, int>, annotator_ids: array<int, int>, annotation_task_ids: array<int, int>, dataset_ids: array<int, int>, role: string} $data */
            $user = $this->createManager($data);
        } else {
            throw new InvalidArgumentException('Unknown role');
        }

        // Welcome the newly-created privileged user (admins/managers have an email).
        $user->notify(
            new UserWelcomeNotification($creator, $role)->locale(app()->getLocale())
        );

        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User {
        $roleValue = $data['role'] ?? null;
        $role = is_string($roleValue) ? RolesEnum::tryFrom($roleValue) : null;

        if ($role === RolesEnum::ANNOTATOR) {
            /** @var array{name: string, username: string, password?: string, manager_ids: array<int, int>, role: string} $data */
            return $this->updateAnnotator($user, $data);
        }

        if ($role === RolesEnum::ADMIN) {
            /** @var array{name: string, username: string, email: string, password?: string, project_ids: array<int, int>, annotator_ids: array<int, int>, role: string} $data */
            return $this->updateAdmin($user, $data);
        }

        if ($role === RolesEnum::ANNOTATION_MANAGER) {
            /** @var array{name: string, username: string, email: string, password?: string, project_ids: array<int, int>, annotator_ids: array<int, int>, annotation_task_ids: array<int, int>, dataset_ids: array<int, int>, role: string} $data */
            return $this->updateManager($user, $data);
        }

        throw new InvalidArgumentException('Unknown role');
    }

    public function handleDelete(User $user): void {
        if ($user->status === StatusEnum::PENDING) {
            $this->hardDelete($user);
        } else {
            $this->softDelete($user);
        }
    }

    /**
     * @param  array<int, int>  $userIds
     * @param  array<int, int>|null  $subProjectIds  When provided, restrict to these subprojects only
     *
     * @return array<int, array{total_workload: int, workload_per_subproject: array<int, int>}>
     */
    public function getWorkloads(array $userIds, ?array $subProjectIds = null): array {
        return $this->getUserWorkloadsQuery->get($userIds, $subProjectIds);
    }

    public function handleFirstLogin(User $user): void {
        if ($user->status !== StatusEnum::PENDING) {
            return;
        }

        $this->activate($user);
    }

    public function findByEmail(string $email): ?User {
        return $this->findUserByEmailQuery->get($email);
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(?string $search = null): Collection {
        return $this->getUsersQuery->get($search);
    }

    /**
     * @return Collection<int, array{name: string, label: string}>
     *
     * @phpstan-return Collection<int, array{name: string, label: string}>
     */
    public function getRolesForForm(): Collection {
        /** @var User $user */
        $user = auth()->user();

        // Annotation managers can assign annotators and other annotation managers, but not admins
        $cases = $user->hasRole(RolesEnum::ADMIN->value)
            ? RolesEnum::cases()
            : [RolesEnum::ANNOTATION_MANAGER, RolesEnum::ANNOTATOR];

        return collect($cases)->map(fn (RolesEnum $rolesEnum): array => [
            'name' => $rolesEnum->value,
            'label' => 'roles.' . $rolesEnum->value,
        ])->values();
    }

    private function activate(User $user): void {
        $user->update(['status' => StatusEnum::ACTIVE]);
    }

    private function hardDelete(User $user): void {
        // All FK relationships (pivot tables, assignments) are covered by DB-level
        // cascadeOnDelete() constraints. Spatie roles are cleaned via model events.
        $user->forceDelete();
    }

    private function softDelete(User $user): void {
        $suffix = '_del_' . $this->findNextDeletionIndexQuery->find($user->username);

        $user->name .= $suffix;
        $user->email = $user->email !== null ? $user->email . $suffix : null;
        $user->username .= $suffix;
        $user->password = null;
        $user->remember_token = null;
        $user->status = StatusEnum::INACTIVE;
        $user->save();

        $user->delete();
    }

    /**
     * @param  array{name: string, username: string, password?: string, manager_ids: array<int, int>, role: string}  $data
     */
    private function updateAnnotator(User $user, array $data): User {
        $fields = ['name' => $data['name'], 'username' => $data['username']];
        if (isset($data['password'])) {
            $fields['password'] = $data['password'];
        }

        $user->update($fields);
        $user->syncRoles([RolesEnum::ANNOTATOR]);

        $this->syncManagersForAnnotatorQuery->sync(
            annotatorId: $user->id,
            managerIds: $data['manager_ids'],
        );

        return $user;
    }

    /**
     * @param  array{name: string, username: string, email: string, password?: string, project_ids: array<int, int>, annotator_ids: array<int, int>, role: string}  $data
     */
    private function updateAdmin(User $user, array $data): User {
        $fields = ['name' => $data['name'], 'username' => $data['username'], 'email' => $data['email']];
        if (isset($data['password'])) {
            $fields['password'] = $data['password'];
        }

        $user->update($fields);
        $user->syncRoles([RolesEnum::ADMIN]);

        $this->syncProjectsForManagerQuery->sync(managerId: $user->id, projectIds: $data['project_ids']);
        $this->syncAnnotatorsForManagerQuery->sync(managerId: $user->id, annotatorIds: $data['annotator_ids']);

        return $user;
    }

    /**
     * @param  array{name: string, username: string, email: string, password?: string, project_ids: array<int, int>, annotator_ids: array<int, int>, annotation_task_ids: array<int, int>, dataset_ids: array<int, int>, role: string}  $data
     */
    private function updateManager(User $user, array $data): User {
        $fields = ['name' => $data['name'], 'username' => $data['username'], 'email' => $data['email']];
        if (isset($data['password'])) {
            $fields['password'] = $data['password'];
        }

        $user->update($fields);
        $user->syncRoles([RolesEnum::ANNOTATION_MANAGER]);

        $this->syncProjectsForManagerQuery->sync(managerId: $user->id, projectIds: $data['project_ids']);
        $this->syncAnnotatorsForManagerQuery->sync(managerId: $user->id, annotatorIds: $data['annotator_ids']);
        $this->syncAnnotationTasksForManagerQuery->sync(managerId: $user->id, annotationTaskIds: $data['annotation_task_ids']);

        $taskDatasetIds = $this->getDatasetIdsByAnnotationTaskIdsQuery->get($data['annotation_task_ids']);
        $filteredDatasetIds = array_values(array_intersect($data['dataset_ids'], $taskDatasetIds));
        $this->syncDatasetsForManagerQuery->sync(managerId: $user->id, datasetIds: $filteredDatasetIds);

        return $user;
    }

    /**
     * @param  array{name: string, username: string, password: string, password_confirmation: string, manager_ids: array<int, int>, role: string}  $data
     *
     * @throws UserCreationException
     */
    private function createAnnotator(array $data): User {
        if ($this->findUserByNameQuery->exists($data['name'])) {
            throw UserCreationException::duplicateName();
        }

        if ($this->findUserByUsernameQuery->exists($data['username'])) {
            throw UserCreationException::duplicateUsername();
        }

        if ($data['password'] !== $data['password_confirmation']) {
            throw UserCreationException::passwordMismatch();
        }

        $user = $this->createAnnotatorQuery->create(
            name: $data['name'],
            username: $data['username'],
            password: $data['password'],
        );

        $this->connectAnnotatorToManagersQuery->connect(
            annotatorId: $user->id,
            managerIds: $data['manager_ids'],
        );

        return $user;
    }

    /**
     * @param  array{name: string, username: string, email: string, password: string, password_confirmation: string, project_ids: array<int, int>, annotator_ids: array<int, int>, role: string}  $data
     *
     * @throws UserCreationException
     */
    private function createAdmin(array $data): User {
        if ($this->findUserByNameQuery->exists($data['name'])) {
            throw UserCreationException::duplicateName();
        }

        if ($this->findUserByUsernameQuery->exists($data['username'])) {
            throw UserCreationException::duplicateUsername();
        }

        if ($this->findUserByEmailQuery->exists($data['email'])) {
            throw UserCreationException::duplicateEmail();
        }

        if ($data['password'] !== $data['password_confirmation']) {
            throw UserCreationException::passwordMismatch();
        }

        $user = $this->createAdminQuery->create(
            name: $data['name'],
            username: $data['username'],
            email: $data['email'],
            password: $data['password'],
        );

        $this->connectManagerToProjectsQuery->connect(
            managerId: $user->id,
            projectIds: $data['project_ids'],
        );

        $this->connectManagerToAnnotatorsQuery->connect(
            managerId: $user->id,
            annotatorIds: $data['annotator_ids'],
        );

        return $user;
    }

    /**
     * @param  array{name: string, username: string, email: string, password: string, password_confirmation: string, project_ids: array<int, int>, annotator_ids: array<int, int>, annotation_task_ids: array<int, int>, dataset_ids: array<int, int>, role: string}  $data
     *
     * @throws UserCreationException
     */
    private function createManager(array $data): User {
        if ($this->findUserByNameQuery->exists($data['name'])) {
            throw UserCreationException::duplicateName();
        }

        if ($this->findUserByUsernameQuery->exists($data['username'])) {
            throw UserCreationException::duplicateUsername();
        }

        if ($this->findUserByEmailQuery->exists($data['email'])) {
            throw UserCreationException::duplicateEmail();
        }

        if ($data['password'] !== $data['password_confirmation']) {
            throw UserCreationException::passwordMismatch();
        }

        $user = $this->createManagerQuery->create(
            name: $data['name'],
            username: $data['username'],
            email: $data['email'],
            password: $data['password'],
        );

        $this->connectManagerToProjectsQuery->connect(
            managerId: $user->id,
            projectIds: $data['project_ids'],
        );

        $this->connectManagerToAnnotatorsQuery->connect(
            managerId: $user->id,
            annotatorIds: $data['annotator_ids'],
        );

        $this->connectManagerToAnnotationTasksQuery->connect(
            managerId: $user->id,
            annotationTaskIds: $data['annotation_task_ids'],
        );

        $this->connectManagerToDatasetsQuery->connect(
            managerId: $user->id,
            datasetIds: $data['dataset_ids'],
        );

        return $user;
    }
}
