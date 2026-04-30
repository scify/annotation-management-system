<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\ProjectStatusEnum;
use App\Enums\UserRelationsEnum;
use App\Models\Project;
use App\Models\UserRelation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DashboardService {
    /**
     * @return Collection<int, Project>
     */
    public function getAllInProgressProjects(): Collection {
        return Project::query()->where('status', ProjectStatusEnum::IN_PROGRESS)->get();
    }

    /**
     * @return Collection<int, Project>
     */
    public function getMyInProgressProjects(int $userId): Collection {
        $collaboratorOwnerIds = UserRelation::query()->where('related_user_id', $userId)
            ->where('relation_type', UserRelationsEnum::COLLABORATOR_OF_USER)
            ->select('user_id');

        return Project::query()->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->where(function (Builder $query) use ($userId, $collaboratorOwnerIds): void {
                $query->where('owner_user_id', $userId)
                    ->orWhereIn('owner_user_id', $collaboratorOwnerIds);
            })
            ->get();
    }
}
