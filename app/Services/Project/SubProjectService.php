<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Models\AnnotationAssignment;
use App\Models\SubProject;
use App\Queries\GetProgressQuery;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

readonly class SubProjectService {
    public function __construct(
        private GetProgressQuery $progressQuery,
    ) {}

    /**
     * @param  array<int, int>  $subProjectIds
     *
     * @return array<int, array{
     *     progress: float,
     *     assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>
     * }>
     */
    public function getProgress(array $subProjectIds): array {
        if ($subProjectIds === []) {
            return [];
        }

        /** @var array<int, array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>> $assignmentsBySubProject */
        $assignmentsBySubProject = array_fill_keys($subProjectIds, []);

        /** @var Collection<int, AnnotationAssignment> $assignmentRows */
        $assignmentRows = AnnotationAssignment::query()
            ->whereIn('sub_project_id', $subProjectIds)
            ->select('id', 'sub_project_id', 'user_id')
            ->get();

        $assignmentToSubProject = [];
        $assignmentUsers = [];

        foreach ($assignmentRows as $row) {
            $assignmentToSubProject[$row->id] = $row->sub_project_id;
            $assignmentUsers[$row->id] = $row->user_id;
        }

        $allIds = array_keys($assignmentToSubProject);

        if ($allIds !== []) {
            foreach ($this->progressQuery->get($allIds) as $assignmentId => $data) {
                $spId = $assignmentToSubProject[$assignmentId];
                $assignmentsBySubProject[$spId][$assignmentId] = [...$data, 'user_id' => $assignmentUsers[$assignmentId]];
            }
        }

        $result = [];

        foreach ($subProjectIds as $spId) {
            $assignments = $assignmentsBySubProject[$spId];
            $totalAll = array_sum(array_column($assignments, 'annotations_all'));
            $totalDone = array_sum(array_column($assignments, 'annotations_done'));
            $result[$spId] = [
                'progress' => $totalAll > 0 ? (float) ($totalDone / $totalAll) : 0.0,
                'assignments' => $assignments,
            ];
        }

        return $result;
    }

    /**
     * @param  Collection<int, SubProject>  $subProjects
     *
     * @return array<int, array{id: int, name: string, status: string, scheduled_at: Carbon|null, deadline_at: Carbon|null, started_at: Carbon|null, completed_at: Carbon|null, progress: float, annotators_count: int, first_instance_index: int, last_instance_index: int}>
     */
    public function getSubProjectsData(Collection $subProjects): array {
        /** @var array<int, int> $subProjectIds */
        $subProjectIds = $subProjects->pluck('id')->all();
        $progressBySubProject = $this->getProgress($subProjectIds);
        $notificationCounts = $this->getNotificationCounts($subProjectIds);

        return $subProjects->map(function (SubProject $subProject) use ($progressBySubProject, $notificationCounts): array {
            $spProgress = $progressBySubProject[$subProject->id] ?? ['progress' => 0.0, 'assignments' => []];

            return [
                'id' => $subProject->id,
                'name' => $subProject->name,
                'status' => $subProject->status->value,
                'scheduled_at' => $subProject->scheduled_at,
                'deadline_at' => $subProject->deadline_at,
                'started_at' => $subProject->started_at,
                'completed_at' => $subProject->completed_at,
                'progress' => $spProgress['progress'],
                'annotators_count' => count($spProgress['assignments']),
                'first_instance_index' => $subProject->first_instance_index,
                'last_instance_index' => $subProject->last_instance_index,
                'notification_count' => $notificationCounts[$subProject->id] ?? 0,
            ];
        })->values()->all();
    }

    /**
     * TODO: implement once notifications are available.
     *
     * @param  array<int, int>  $subProjectIds
     *
     * @return array<int, int>
     */
    private function getNotificationCounts(array $subProjectIds): array {
        return array_fill_keys($subProjectIds, 0);
    }
}
