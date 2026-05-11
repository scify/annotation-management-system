<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectStatusEnum;
use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\SubProject;

final readonly class GetUserWorkloadsQuery {
    /**
     * @param  array<int, mixed>  $userIds
     *
     * @return array<int, int>
     */
    public function get(array $userIds): array {
        if ($userIds === []) {
            return [];
        }

        $subProjects = SubProject::query()
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
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

        $annotationCountsByAssignment = Annotation::query()
            ->whereIn('annotation_assignment_id', $annotationAssignments->pluck('id'))
            ->selectRaw('annotation_assignment_id, COUNT(*) as count')
            ->groupBy('annotation_assignment_id')
            ->pluck('count', 'annotation_assignment_id');

        $assignmentsByUser = $annotationAssignments->groupBy('user_id');

        $workloads = [];
        foreach ($userIds as $userId) {
            $userAssignments = $assignmentsByUser->get($userId, collect());
            $sumEffort = 0;
            $sumWorkDone = 0;

            foreach ($userAssignments as $assignment) {
                /** @var AnnotationAssignment $assignment */
                $subProject = $subProjects->get($assignment->sub_project_id);
                if (! $subProject instanceof SubProject) {
                    continue;
                }

                $weight = $subProject->project->annotationTask->weight;
                $effort = ($subProject->last_instance_index - $subProject->first_instance_index) * $weight;
                $sumEffort += $effort;
                $workDone = (int) $annotationCountsByAssignment->get($assignment->getKey(), 0);
                $sumWorkDone += ($workDone * $weight);
            }

            $workloads[(int) $userId] = $sumEffort - $sumWorkDone;
        }

        return $workloads;
    }
}
