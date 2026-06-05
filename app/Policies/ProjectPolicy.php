<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RolesEnum;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy {
    public function viewAny(User $user): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return $user->hasRole(RolesEnum::ANNOTATION_MANAGER);
    }

    public function create(User $user): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return $user->hasRole(RolesEnum::ANNOTATION_MANAGER);
    }

    public function view(User $user, Project $project): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return $user->hasRole(RolesEnum::ANNOTATION_MANAGER);
    }

    public function export(User $user): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return $user->hasRole(RolesEnum::ANNOTATION_MANAGER);
    }

    public function toggleCanFlag(User $user): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return $user->hasRole(RolesEnum::ANNOTATION_MANAGER);
    }

    public function detachAnnotator(User $user): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return $user->hasRole(RolesEnum::ANNOTATION_MANAGER);
    }
}
