<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Enums\RolesEnum;
use App\Enums\UserRelationsEnum;
use App\Models\User;

class UserPolicy {
    public function view(User $user, User $model): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }
        if ($user->hasRole(RolesEnum::ANNOTATOR)) {
            return false;
        }

        return $user->relatedUsers()->where('id', $model->id)->wherePivot('relation_type', UserRelationsEnum::ANNOTATOR_OF_ANNOTATION_POOL)->exists();
    }

    public function create(User $user): bool {
        return $user->can(PermissionsEnum::CREATE_USERS->value);
    }

    public function update(User $user, User $model): bool {
        // Admin can update anyone
        if ($user->hasRole(RolesEnum::ADMIN->value)) {
            return true;
        }

        // Annotation managers can't update admins
        if ($model->hasRole(RolesEnum::ADMIN->value)) {
            return false;
        }

        return $user->can(PermissionsEnum::UPDATE_USERS->value);
    }

    public function delete(User $user, User $model): bool {
        // Admin can delete anyone
        if ($user->hasRole(RolesEnum::ADMIN->value)) {
            return true;
        }

        // Annotation managers can't delete admins
        if ($model->hasRole(RolesEnum::ADMIN->value)) {
            return false;
        }

        return $user->can(PermissionsEnum::DELETE_USERS->value);
    }

    public function restore(User $user): bool {
        return $user->can(PermissionsEnum::RESTORE_USERS->value);
    }
}
