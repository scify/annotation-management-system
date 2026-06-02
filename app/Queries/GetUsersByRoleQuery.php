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
}
