<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Exceptions\UserCreationException;
use App\Models\User;
use App\Queries\ConnectAnnotatorToManagersQuery;
use App\Queries\CreateAnnotatorQuery;
use App\Queries\FindUserByEmailQuery;
use App\Queries\FindUserByNameQuery;
use App\Queries\FindUserByUsernameQuery;
use App\Queries\GetUsersQuery;
use App\Queries\GetWorkloadsByAnnotatorsQuery;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;

readonly class UserService {
    public function __construct(
        private FindUserByEmailQuery $findUserByEmailQuery,
        private FindUserByNameQuery $findUserByNameQuery,
        private FindUserByUsernameQuery $findUserByUsernameQuery,
        private GetWorkloadsByAnnotatorsQuery $getUserWorkloadsQuery,
        private GetUsersQuery $getUsersQuery,
        private CreateAnnotatorQuery $createAnnotatorQuery,
        private ConnectAnnotatorToManagersQuery $connectAnnotatorToManagersQuery,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws UserCreationException
     */
    public function create(array $data): User {
        $roleValue = $data['role'] ?? null;
        $role = is_string($roleValue) ? RolesEnum::tryFrom($roleValue) : null;

        if ($role === RolesEnum::ANNOTATOR) {
            /** @var array{name: string, username: string, password: string, password_confirmation: string, manager_ids: array<int, int>, role: string} $data */
            return $this->createAnnotator($data);
        }

        return match ($role) {
            RolesEnum::ADMIN => $this->createAdmin($data),
            RolesEnum::ANNOTATION_MANAGER => $this->createManager($data),
            default => throw new InvalidArgumentException('Unknown role'),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User {
        $role = $data['role'] ?? $user->getRoleNames()->first();
        unset($data['role']);

        $user->update($data);
        $user->syncRoles([$role]);

        return $user;
    }

    public function delete(User $user): ?bool {
        return $user->delete();
    }

    public function restore(User $user): User {
        $user->restore();

        return $user;
    }

    /**
     * @param  array<int, int>  $userIds
     *
     * @return array<int, array{total_workload: int, workload_per_subproject: array<int, int>}>
     */
    public function getWorkloads(array $userIds): array {
        return $this->getUserWorkloadsQuery->get($userIds);
    }

    public function activateIfPending(User $user): void {
        if ($user->status === StatusEnum::PENDING) {
            $user->update(['status' => StatusEnum::ACTIVE]);
        }
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
     * @param  array<string, mixed>  $data
     */
    private function createAdmin(array $data): User {
        $user = User::query()->create(Arr::only($data, ['name', 'username', 'email', 'password']));
        $user->syncRoles([RolesEnum::ADMIN]);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createManager(array $data): User {
        $user = User::query()->create(Arr::only($data, ['name', 'username', 'email', 'password']));
        $user->syncRoles([RolesEnum::ANNOTATION_MANAGER]);

        return $user;
    }
}
