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

    public function attachSubProjectAnnotators(User $user, SubProject $subProject): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return ProjectManager::query()
            ->where('project_id', $subProject->project_id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function attachAnnotators(User $user, Project $project): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return ProjectManager::query()
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->exists();
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

    public function proposeOwnership(User $user, Project $project): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return $project->owner_user_id === $user->id;
    }

    public function acceptOwnership(User $user, Project $project): bool {
        return ProjectManager::query()
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->where('proposed_to_become_owner', true)
            ->exists();
    }

    public function rejectOwnership(User $user, Project $project): bool {
        return ProjectManager::query()
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->where('proposed_to_become_owner', true)
            ->exists();
    }

    public function cancelOwnership(User $user, Project $project): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return $project->owner_user_id === $user->id;
    }

    public function removeManager(User $user, Project $project): bool {
        if ($user->hasRole(RolesEnum::ADMIN)) {
            return true;
        }

        return $project->owner_user_id === $user->id;
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
