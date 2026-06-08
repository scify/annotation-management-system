<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Models\AnnotatorOfManager;
use App\Models\ProjectManager;
use App\Models\User;

class UserPolicy {
    public function viewAny(User $user): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return $user->hasRole(RolesEnum::ANNOTATION_MANAGER);
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
            return ProjectManager::query()
                ->whereIn('project_id',
                    ProjectManager::query()->where('user_id', $user->id)->select('project_id')
                )
                ->where('user_id', $model->id)
                ->exists();
        }

        return AnnotatorOfManager::query()
            ->where('manager_id', $user->id)
            ->where('annotator_id', $model->id)
            ->exists();
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

        return AnnotatorOfManager::query()
            ->where('manager_id', $user->id)
            ->where('annotator_id', $model->id)
            ->exists();
    }

    public function delete(User $user, User $model): bool {
        if ($user->id === $model->id) {
            return false;
        }

        // Hard delete path (PENDING users): all roles allowed
        if ($model->status === StatusEnum::PENDING) {
            return true;
        }

        // Soft delete path (ACTIVE/INACTIVE users): admins only
        return $user->hasRole(RolesEnum::ADMIN);
    }

    public function restore(User $user): bool {
        return $user->hasRole(RolesEnum::ADMIN);
    }
}
