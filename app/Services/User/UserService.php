<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Queries\FindUserByEmailQuery;
use App\Queries\GetUsersQuery;
use App\Queries\GetWorkloadsByAnnotatorsQuery;
use Illuminate\Support\Collection;

readonly class UserService {
    public function __construct(
        private FindUserByEmailQuery $findUserByEmailQuery,
        private GetWorkloadsByAnnotatorsQuery $getUserWorkloadsQuery,
        private GetUsersQuery $getUsersQuery,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User {
        $role = $data['role'] ?? RolesEnum::ANNOTATOR->value;
        unset($data['role']);

        $user = User::query()->create($data);
        $user->syncRoles([$role]);

        return $user;
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
     * @param  array<int, mixed>  $userIds
     *
     * @return array<int, array{total_workload: int, workload_per_subproject: array<int, int>}>
     */
    public function getWorkloads(array $userIds): array {
        return $this->getUserWorkloadsQuery->get($userIds);
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
}
