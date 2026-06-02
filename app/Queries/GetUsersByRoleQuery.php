<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetUsersByRoleQuery {
    /**
     * Returns all non-deleted users with their role resolved in a single JOIN query.
     * Each User instance carries the role as the `role` dynamic attribute.
     *
     * @return Collection<int, User>
     */
    public function get(): Collection {
        /** @var Collection<int, User> $result */
        $result = User::query()
            ->join('model_has_roles', function ($join): void {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', '=', User::class);
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereNull('users.deleted_at')
            ->whereIn('roles.name', array_column(RolesEnum::cases(), 'value'))
            ->select([
                'users.id',
                'users.name',
                'users.username',
                'users.email',
                'users.status',
                'roles.name as role',
            ])
            ->get();

        return $result;
    }

    /**
     * Returns annotators who share at least one project with the given user
     * (both appear in annotator_of_project for the same project_id).
     *
     * @return Collection<int, User>
     */
    public function getMyAnnotators(int $currentUserId): Collection {
        /** @var Collection<int, User> $result */
        $result = User::query()
            ->join('model_has_roles', function ($join): void {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', '=', User::class);
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereNull('users.deleted_at')
            ->where('roles.name', RolesEnum::ANNOTATOR->value)
            ->whereExists(fn ($q) => $q
                ->from('annotator_of_project as aop_annotator')
                ->join('annotator_of_project as aop_current', 'aop_current.project_id', '=', 'aop_annotator.project_id')
                ->whereColumn('aop_annotator.user_id', 'users.id')
                ->where('aop_current.user_id', $currentUserId)
            )
            ->select([
                'users.id',
                'users.name',
                'users.username',
                'users.status',
                'roles.name as role',
            ])
            ->distinct()
            ->get();

        return $result;
    }

    /**
     * Returns all non-deleted annotation managers.
     *
     * @return Collection<int, User>
     */
    public function getAllManagers(): Collection {
        /** @var Collection<int, User> $result */
        $result = User::query()
            ->join('model_has_roles', function ($join): void {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', '=', User::class);
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereNull('users.deleted_at')
            ->where('roles.name', RolesEnum::ANNOTATION_MANAGER->value)
            ->select([
                'users.id',
                'users.name',
                'users.username',
                'users.email',
                'users.status',
                'roles.name as role',
            ])
            ->get();

        return $result;
    }

    /**
     * Returns annotation managers who share at least one project with the given user
     * (both appear in project_managers for the same project_id).
     *
     * @return Collection<int, User>
     */
    public function getMyManagers(int $currentUserId): Collection {
        /** @var Collection<int, User> $result */
        $result = User::query()
            ->join('model_has_roles', function ($join): void {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', '=', User::class);
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereNull('users.deleted_at')
            ->where('roles.name', RolesEnum::ANNOTATION_MANAGER->value)
            ->whereExists(fn ($q) => $q
                ->from('project_managers as pm_manager')
                ->join('project_managers as pm_current', 'pm_current.project_id', '=', 'pm_manager.project_id')
                ->whereColumn('pm_manager.user_id', 'users.id')
                ->where('pm_current.user_id', $currentUserId)
            )
            ->select([
                'users.id',
                'users.name',
                'users.username',
                'users.email',
                'users.status',
                'roles.name as role',
            ])
            ->distinct()
            ->get();

        return $result;
    }
}
