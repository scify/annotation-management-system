<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Queries\GetUsersByRoleQuery;
use Illuminate\Support\Collection;

readonly class UserManagementService {
    public function __construct(
        private GetUsersByRoleQuery $getUsersByRoleQuery,
    ) {}

    /**
     * @return array{
     *     admins: array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>,
     *     managers: array<int, array{id: int, name: string, username: string, email: string, status: string, role: string}>,
     *     annotators: array<int, array{id: int, name: string, username: string, status: string, role: string}>
     * }
     */
    public function getUsersByRole(): array {
        $all = $this->getUsersByRoleQuery->get();

        return [
            'admins' => $this->mapWithEmail(
                $all->filter(fn (User $u): bool => $u->role === RolesEnum::ADMIN->value)
            ),
            'managers' => $this->mapWithEmail(
                $all->filter(fn (User $u): bool => $u->role === RolesEnum::ANNOTATION_MANAGER->value)
            ),
            'annotators' => $this->mapWithoutEmail(
                $all->filter(fn (User $u): bool => $u->role === RolesEnum::ANNOTATOR->value)
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
}
