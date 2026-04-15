<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RolesEnum;
use App\Enums\UserRelationsEnum;
use App\Models\User;

class UserPolicy {
    public function viewAny(User $user): bool {
        return $user->hasRole(RolesEnum::ADMIN) || $user->hasRole(RolesEnum::ANNOTATION_MANAGER);
    }

    public function view(User $user, User $model): bool {
        if ($user->id === $model->id) {
            return true;
        }

        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        if ($user->hasRole(RolesEnum::ANNOTATOR)) {
            return false;
        }

        if ($model->hasRole(RolesEnum::ADMIN)) {
            return false;
        }

        if ($model->hasRole(RolesEnum::ANNOTATION_MANAGER)) {
            return $user->relatedUsers()->where('related_user_id', $model->id)->wherePivot('relation_type', UserRelationsEnum::COLLABORATOR_OF_USER)->exists();
        }

        return $user->relatedUsers()->where('related_user_id', $model->id)->wherePivot('relation_type', UserRelationsEnum::ANNOTATOR_OF_MANAGER)->exists();
    }

    public function create(User $user, ?string $targetRole = null): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        // annotation managers can create other annotation managers and annotators (they cannot create admins)
        return $user->hasRole(RolesEnum::ANNOTATION_MANAGER) && ($targetRole === RolesEnum::ANNOTATOR->value || $targetRole === RolesEnum::ANNOTATION_MANAGER->value);
    }

    public function update(User $user, User $model): bool {
        if ($user->id === $model->id) {
            return true;
        }

        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        if ($user->hasRole(RolesEnum::ANNOTATOR)) {
            return false;
        }

        if ($model->hasRole(RolesEnum::ADMIN) || $model->hasRole(RolesEnum::ANNOTATION_MANAGER)) {
            return false;
        }

        return $user->relatedUsers()->where('related_user_id', $model->id)->wherePivot('relation_type', UserRelationsEnum::ANNOTATOR_OF_MANAGER)->exists();

    }

    public function delete(User $user, User $model): bool {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasRole(RolesEnum::ADMIN);
    }

    public function restore(User $user): bool {
        return $user->hasRole(RolesEnum::ADMIN);
    }
}
