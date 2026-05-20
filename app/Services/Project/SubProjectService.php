<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Models\SubProject;
use App\Queries\GetProgressQuery;

readonly class SubProjectService {
    public function __construct(
        private GetProgressQuery $progressQuery,
    ) {}

    public function getWorkload(SubProject $subProject): float {
        return 0.5;
    }

    /**
     * @return array{
     *     progress: float,
     *     assignments: array<int, array{annotations_all: int, annotations_done: int, progress: float}>
     * }
     */
    public function getProgress(SubProject $subProject): array {
        $assignmentIds = $subProject->annotationAssignments()->pluck('id')->all();

        if ($assignmentIds === []) {
            return ['progress' => 0.0, 'assignments' => []];
        }

        $assignments = $this->progressQuery->get($assignmentIds);

        $totalAll = array_sum(array_column($assignments, 'annotations_all'));
        $totalDone = array_sum(array_column($assignments, 'annotations_done'));

        return [
            'progress' => $totalAll > 0 ? $totalDone / $totalAll : 0.0,
            'assignments' => $assignments,
        ];
    }
}
