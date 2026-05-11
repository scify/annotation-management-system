<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Models\SubProject;
use Illuminate\Support\Collection;

final readonly class GetActiveSubProjectIdsQuery {
    /**
     * @return Collection<int, mixed>
     */
    public function get(): Collection {
        return SubProject::query()
            ->join('projects', 'projects.id', '=', 'sub_projects.project_id')
            ->where('projects.status', ProjectStatusEnum::IN_PROGRESS)
            ->where('sub_projects.status', ProjectStatusEnum::IN_PROGRESS)
            ->pluck('sub_projects.id');
    }
}
