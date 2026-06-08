<?php

declare(strict_types=1);

namespace App\Services\Monitor;

use App\Enums\RolesEnum;
use App\Models\AnnotatorOfProject;
use App\Models\User;
use App\Queries\Annotator\GetAnnotatorsByManagerQuery;
use App\Queries\Annotator\GetAnnotatorsQuery;
use App\Queries\Project\GetProjectIdsManagedByUserQuery;
use App\Services\Annotation\AnnotatorStatsService;
use Illuminate\Database\Eloquent\Collection;

readonly class MonitorAnnotatorHistoryTabService {
    public function __construct(
        private AnnotatorStatsService $annotatorStatsService,
        private GetAnnotatorsQuery $allAnnotatorsQuery,
        private GetAnnotatorsByManagerQuery $annotatorsByManagerQuery,
        private GetProjectIdsManagedByUserQuery $projectIdsByManagerQuery,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getData(User $user): array {
        $roleName = $user->getRoleNames()->first();

        if ($roleName === RolesEnum::ANNOTATOR->value) {
            return [];
        }

        if ($roleName === RolesEnum::ADMIN->value) {
            $allAnnotators = $this->allAnnotatorsQuery->getAll();
            $preloaded = $this->annotatorStatsService->preloadData($allAnnotators->pluck('id')->all());
            $myAnnotatorIds = $this->resolveAdminMyAnnotatorIds($user->id, $preloaded);
            /** @var Collection<int, User> $myAnnotators */
            $myAnnotators = $allAnnotators
                ->filter(fn (User $u): bool => in_array($u->id, $myAnnotatorIds, true))
                ->values();

            return [
                'all_annotators' => $this->annotatorStatsService->buildData($allAnnotators, $preloaded),
                'my_annotators' => $this->annotatorStatsService->buildData($myAnnotators, $preloaded),
            ];
        }

        $myAnnotators = $this->annotatorsByManagerQuery->get($user->id);

        return [
            'my_annotators' => $this->annotatorStatsService->buildAnnotatorsData($myAnnotators),
        ];
    }

    /**
     * Returns annotator IDs whose projects overlap with the given admin's managed projects.
     *
     * @param  array<string, mixed>  $preloaded
     *
     * @return array<int, mixed>
     */
    private function resolveAdminMyAnnotatorIds(int $userId, array $preloaded): array {
        /** @var Collection<int, AnnotatorOfProject> $links */
        $links = $preloaded['annotator_project_links'];

        $myProjectIds = $this->projectIdsByManagerQuery->get($userId);

        return $links
            ->filter(fn (AnnotatorOfProject $link): bool => in_array($link->project_id, $myProjectIds, true))
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();
    }
}
