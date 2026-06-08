<?php

declare(strict_types=1);

namespace App\Queries\Dashboard;

use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Models\Project;
use App\Models\User;

final readonly class GetPlatformStatsQuery {
    /**
     * @return array{all_projects: int, all_annotators: int, all_managers: int, all_admins: int}
     */
    public function get(): array {
        $allProjects = Project::query()->count();

        $activeUsers = User::query()
            ->where('status', StatusEnum::ACTIVE)
            ->with('roles')
            ->get();

        $allAnnotators = 0;
        $allManagers = 0;
        $allAdmins = 0;

        foreach ($activeUsers as $user) {
            $roleName = $user->getRoleNames()->first();
            if ($roleName === RolesEnum::ANNOTATOR->value) {
                $allAnnotators++;
            } elseif ($roleName === RolesEnum::ANNOTATION_MANAGER->value) {
                $allManagers++;
            } elseif ($roleName === RolesEnum::ADMIN->value) {
                $allAdmins++;
            }
        }

        return [
            'all_projects' => $allProjects,
            'all_annotators' => $allAnnotators,
            'all_managers' => $allManagers,
            'all_admins' => $allAdmins,
        ];
    }
}
