<?php

declare(strict_types=1);

namespace App\Queries\Annotator;

use App\Enums\ProjectStatusEnum;
use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\SubProject;
use Illuminate\Support\Collection;

final readonly class GetWorkloadsByAnnotatorsQuery {
    /**
     * @param  array<int, int>  $userIds
     * @param  array<int, int>|null  $subProjectIds  When provided, restrict to these subprojects only
     *
     * @return array<int, array{total_workload: int, workload_per_subproject: array<int, int>}>
     */
    public function get(array $userIds, ?array $subProjectIds = null): array {
        if ($userIds === []) {
            return [];
        }

        $subProjects = SubProject::query()
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->when($subProjectIds !== null, fn ($q) => $q->whereIn('id', $subProjectIds))
            ->whereIn('id', function ($query) use ($userIds): void {
                $query->select('sub_project_id')
                    ->from('annotation_assignments')
                    ->whereIn('user_id', $userIds);
            })
            ->with('project.annotationTask')
            ->get()
            ->keyBy('id');

        $annotationAssignments = AnnotationAssignment::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('sub_project_id', $subProjects->keys())
            ->get();

        /** @var Collection<int|string, string|int> $submittedCounts */
        $submittedCounts = Annotation::query()
            ->whereIn('annotation_assignment_id', $annotationAssignments->pluck('id'))
            ->whereNotNull('annotations')
            ->where('pending', false)
            ->selectRaw('annotation_assignment_id, COUNT(*) as count')
            ->groupBy('annotation_assignment_id')
            ->pluck('count', 'annotation_assignment_id');

        /** @var Collection<int|string, string|int> $pendingCounts */
        $pendingCounts = Annotation::query()
            ->whereIn('annotation_assignment_id', $annotationAssignments->pluck('id'))
            ->whereNotNull('annotations')
            ->where('pending', true)
            ->selectRaw('annotation_assignment_id, COUNT(*) as count')
            ->groupBy('annotation_assignment_id')
            ->pluck('count', 'annotation_assignment_id');

        $assignmentsByUser = $annotationAssignments->groupBy('user_id');

        $workloads = [];
        foreach ($userIds as $userId) {
            $userAssignments = $assignmentsByUser->get($userId, collect());
            $sumEffort = 0;
            $sumWorkDone = 0;
            $workloadPerSubproject = [];

            foreach ($userAssignments as $assignment) {
                /** @var AnnotationAssignment $assignment */
                $subProject = $subProjects->get($assignment->sub_project_id);
                if (! $subProject instanceof SubProject) {
                    continue;
                }

                $weight = $subProject->project->annotationTask->weight;
                $effort = ($subProject->last_instance_index - $subProject->first_instance_index + 1) * $weight;
                $submitted = (int) $submittedCounts->get($assignment->id, 0);
                $pending = (int) $pendingCounts->get($assignment->id, 0);
                $workDone = (int) floor(($submitted + $pending * 0.5) * $weight);

                $sumEffort += $effort;
                $sumWorkDone += $workDone;
                $workloadPerSubproject[$assignment->sub_project_id] = $effort - $workDone;
            }

            $workloads[$userId] = [
                'total_workload' => $sumEffort - $sumWorkDone,
                'workload_per_subproject' => $workloadPerSubproject,
            ];
        }

        return $workloads;
    }
}
