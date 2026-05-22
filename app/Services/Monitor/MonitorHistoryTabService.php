<?php

declare(strict_types=1);

namespace App\Services\Monitor;

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\AnnotationAssignment;
use App\Models\AnnotatorOfProject;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use App\Queries\GetAnnotatorProjectLinksByAnnotatorsQuery;
use App\Queries\GetAnnotatorsByManagerQuery;
use App\Queries\GetAnnotatorsQuery;
use App\Queries\GetAssignmentsBySubProjectsAndAnnotatorsQuery;
use App\Queries\GetAverageConfidencePerSubProjectQuery;
use App\Queries\GetCountsOfFlagsQuery;
use App\Queries\GetProjectIdsManagedByUserQuery;
use App\Queries\GetProjectsByIdsQuery;
use App\Queries\GetSubProjectsOfProjectsQuery;
use App\Services\Project\SubProjectService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

readonly class MonitorHistoryTabService {
    public function __construct(
        private SubProjectService $subProjectService,
        private GetAnnotatorsQuery $allAnnotatorsQuery,
        private GetAnnotatorsByManagerQuery $annotatorsByManagerQuery,
        private GetAnnotatorProjectLinksByAnnotatorsQuery $annotatorProjectLinksQuery,
        private GetProjectsByIdsQuery $projectsByIdsQuery,
        private GetSubProjectsOfProjectsQuery $subProjectsByProjectsQuery,
        private GetAssignmentsBySubProjectsAndAnnotatorsQuery $assignmentsBySubProjectsAndAnnotatorsQuery,
        private GetProjectIdsManagedByUserQuery $projectIdsByManagerQuery,
        private GetCountsOfFlagsQuery $flagsQuery,
        private GetAverageConfidencePerSubProjectQuery $avgConfidenceQuery,
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
            $preloaded = $this->preloadData($allAnnotators->pluck('id')->all());
            $myAnnotatorIds = $this->resolveAdminMyAnnotatorIds($user->id, $preloaded);
            /** @var Collection<int, User> $myAnnotators */
            $myAnnotators = $allAnnotators
                ->filter(fn (User $u): bool => in_array($u->id, $myAnnotatorIds, true))
                ->values();

            return [
                'all_annotators' => $this->buildAnnotatorsData($allAnnotators, $preloaded),
                'my_annotators' => $this->buildAnnotatorsData($myAnnotators, $preloaded),
            ];
        }

        $myAnnotators = $this->annotatorsByManagerQuery->get($user->id);
        $preloaded = $this->preloadData($myAnnotators->pluck('id')->all());

        return [
            'my_annotators' => $this->buildAnnotatorsData($myAnnotators, $preloaded),
        ];
    }

    /**
     * Loads all projects and subprojects regardless of status; buildAnnotatorsData filters
     * to IN_PROGRESS and COMPLETED only.
     *
     * @param  array<int, mixed>  $annotatorIds
     *
     * @return array<string, mixed>
     */
    private function preloadData(array $annotatorIds): array {
        $annotatorProjectLinks = $this->annotatorProjectLinksQuery->get($annotatorIds);

        $projectIds = $annotatorProjectLinks->pluck('project_id')->unique()->all();

        /** @var Collection<int, Project> $projects */
        $projects = $this->projectsByIdsQuery->get($projectIds);

        $loadedProjectIds = $projects->pluck('id')->all();

        /** @var Collection<int, SubProject> $subProjects */
        $subProjects = $this->subProjectsByProjectsQuery->get($loadedProjectIds);

        /** @var array<int, int> $subProjectIds */
        $subProjectIds = $subProjects->pluck('id')->all();

        $assignments = $this->assignmentsBySubProjectsAndAnnotatorsQuery->get($subProjectIds, $annotatorIds);

        $progressBySubProject = $this->subProjectService->getProgress($subProjectIds);
        $flagsByUserAndSp = $this->flagsQuery->get($annotatorIds);
        $avgConfidenceByUserAndSp = $this->avgConfidenceQuery->get($annotatorIds);

        return [
            'annotator_project_links' => $annotatorProjectLinks,
            'projects_by_id' => $projects->keyBy('id'),
            'subprojects_by_project' => $subProjects->groupBy('project_id'),
            'assignments_by_annotator' => $assignments->groupBy('user_id'),
            'progress_by_subproject' => $progressBySubProject,
            'flags_by_user_and_sp' => $flagsByUserAndSp,
            'avg_confidence_by_user_and_sp' => $avgConfidenceByUserAndSp,
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

    /**
     * @param  Collection<int, User>  $annotators
     * @param  array<string, mixed>  $preloaded
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildAnnotatorsData(Collection $annotators, array $preloaded): array {
        /** @var Collection<int, AnnotatorOfProject> $annotatorProjectLinks */
        $annotatorProjectLinks = $preloaded['annotator_project_links'];
        /** @var Collection<int, Project> $projectsById */
        $projectsById = $preloaded['projects_by_id'];
        /** @var SupportCollection<int|string, Collection<int, SubProject>> $subProjectsByProject */
        $subProjectsByProject = $preloaded['subprojects_by_project'];
        /** @var SupportCollection<int|string, Collection<int, AnnotationAssignment>> $assignmentsByAnnotator */
        $assignmentsByAnnotator = $preloaded['assignments_by_annotator'];
        /** @var array<int, array{progress: float, assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>}> $progressBySubProject */
        $progressBySubProject = $preloaded['progress_by_subproject'];
        /** @var array<int, array<int, int>> $flagsByUserAndSp */
        $flagsByUserAndSp = $preloaded['flags_by_user_and_sp'];
        /** @var array<int, array<int, float>> $avgConfidenceByUserAndSp */
        $avgConfidenceByUserAndSp = $preloaded['avg_confidence_by_user_and_sp'];

        /** @var SupportCollection<int|string, Collection<int, AnnotatorOfProject>> $linksByAnnotator */
        $linksByAnnotator = $annotatorProjectLinks->groupBy('user_id');

        $historyStatuses = [ProjectStatusEnum::IN_PROGRESS, ProjectStatusEnum::COMPLETED];

        // Pre-build [spId][userId] => annotations_done to avoid per-iteration scanning.
        /** @var array<int, array<int, int>> $doneBySpAndUser */
        $doneBySpAndUser = [];
        foreach ($progressBySubProject as $spId => $spData) {
            foreach ($spData['assignments'] as $assignment) {
                $uid = $assignment['user_id'];
                $doneBySpAndUser[$spId][$uid] = ($doneBySpAndUser[$spId][$uid] ?? 0) + $assignment['annotations_done'];
            }
        }

        $result = [];

        foreach ($annotators as $annotator) {
            /** @var Collection<int, AnnotatorOfProject> $myLinks */
            $myLinks = $linksByAnnotator->get($annotator->id) ?? new Collection();
            $myProjectIds = $myLinks->pluck('project_id')->all();

            /** @var Collection<int, AnnotationAssignment> $myAssignments */
            $myAssignments = $assignmentsByAnnotator->get($annotator->id) ?? new Collection();
            $mySubProjectIds = $myAssignments->pluck('sub_project_id')->all();

            /** @var Collection<int, Project> $myProjects */
            $myProjects = $projectsById->filter(
                fn (Project $p): bool => in_array($p->id, $myProjectIds, true)
                    && in_array($p->status, $historyStatuses, true)
            );

            $subprojectsData = [];

            foreach ($myProjects as $project) {
                /** @var Collection<int, SubProject> $allProjectSubProjects */
                $allProjectSubProjects = $subProjectsByProject->get($project->id) ?? new Collection();

                /** @var Collection<int, SubProject> $annotatorSubProjects */
                $annotatorSubProjects = $allProjectSubProjects->filter(
                    fn (SubProject $sp): bool => in_array($sp->id, $mySubProjectIds, true)
                        && in_array($sp->status, $historyStatuses, true)
                );

                foreach ($annotatorSubProjects as $subProject) {
                    $rawAvg = $avgConfidenceByUserAndSp[$annotator->id][$subProject->id] ?? null;
                    $subprojectsData[] = [
                        'project_name' => $project->name,
                        'subproject_name' => $subProject->name,
                        'completed_at' => $subProject->completed_at,
                        'annotations' => $doneBySpAndUser[$subProject->id][$annotator->id] ?? 0,
                        'flags' => $flagsByUserAndSp[$annotator->id][$subProject->id] ?? 0,
                        'avg_confidence' => $rawAvg !== null ? $this->resolveConfidenceLabel($rawAvg) : null,
                    ];
                }
            }

            $result[] = [
                'id' => $annotator->id,
                'username' => $annotator->username,
                'is_active' => $annotator->is_active,
                'total_projects' => $myProjects->count(),
                'total_subprojects' => count($subprojectsData),
                'total_annotations' => array_sum(array_column($subprojectsData, 'annotations')),
                'total_flags' => array_sum(array_column($subprojectsData, 'flags')),
                'subprojects' => $subprojectsData,
            ];
        }

        return $result;
    }

    private function resolveConfidenceLabel(float $avg): string {
        if ($avg < 0.3) {
            return 'low';
        }

        if ($avg > 0.7) {
            return 'high';
        }

        return 'medium';
    }
}
