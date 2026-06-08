<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\SubProject;
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

    public function updateStatus(User $user, Project $project): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return ProjectManager::query()
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function delete(User $user, Project $project): bool {
        if ($project->status !== ProjectStatusEnum::PENDING) {
            return false;
        }

        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return ProjectManager::query()
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function deleteSubProject(User $user, SubProject $subProject): bool {
        if ($subProject->status !== ProjectStatusEnum::PENDING) {
            return false;
        }

        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return ProjectManager::query()
            ->where('project_id', $subProject->project_id)
            ->where('user_id', $user->id)
            ->exists();
    }
}
