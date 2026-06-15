<?php

declare(strict_types=1);

namespace App\Queries\SubProject;

use App\Enums\ProjectStatusEnum;
use App\Models\SubProject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final readonly class GetSubProjectsForAnnotatorQuery {
    /**
     * Subprojects an annotator should see on their dashboard.
     *
     * @return Collection<int, SubProject>
     */
    public function get(User $user): Collection {
        Log::debug('Getting subprojects for annotator dashboard', ['user_id' => $user->id]);

        // TODO @akosmo: currently returns ALL in-progress subprojects.
        /** @var Collection<int, SubProject> */
        return SubProject::query()
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->with('project.annotationTask')
            ->get();
    }
}
