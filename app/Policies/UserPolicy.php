<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RolesEnum;
use App\Models\AnnotatorOfManager;
use App\Models\Comanager;
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
            return Comanager::query()->where(function ($q) use ($user, $model): void {
                $q->where('user_id', $user->id)
                    ->whereHas('project', fn ($pq) => $pq->where('owner_user_id', $model->id));
            })->orWhere(function ($q) use ($user, $model): void {
                $q->where('user_id', $model->id)
                    ->whereHas('project', fn ($pq) => $pq->where('owner_user_id', $user->id));
            })->exists();
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

        return $user->hasRole(RolesEnum::ADMIN);
    }

    public function restore(User $user): bool {
        return $user->hasRole(RolesEnum::ADMIN);
    }
}
