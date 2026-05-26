<?php

declare(strict_types=1);

namespace App\Services\Monitor;

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Models\AnnotationAssignment;
use App\Models\AnnotatorOfProject;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\SubProject;
use App\Models\User;
use App\Queries\GetAnnotatorProjectLinksByAnnotatorsQuery;
use App\Queries\GetAnnotatorsByManagerQuery;
use App\Queries\GetAnnotatorsQuery;
use App\Queries\GetAssignmentsBySubProjectsAndAnnotatorsQuery;
use App\Queries\GetProjectIdsManagedByUserQuery;
use App\Queries\GetProjectsByIdsQuery;
use App\Queries\GetSubProjectsOfProjectsQuery;
use App\Services\Annotator\WorkloadService;
use App\Services\Project\SubProjectService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

readonly class MonitorActiveWorkTabService {
    public function __construct(
        private SubProjectService $subProjectService,
        private WorkloadService $workloadService,
        private GetAnnotatorsQuery $allAnnotatorsQuery,
        private GetAnnotatorsByManagerQuery $annotatorsByManagerQuery,
        private GetAnnotatorProjectLinksByAnnotatorsQuery $annotatorProjectLinksQuery,
        private GetProjectsByIdsQuery $projectsByIdsQuery,
        private GetSubProjectsOfProjectsQuery $subProjectsByProjectsQuery,
        private GetAssignmentsBySubProjectsAndAnnotatorsQuery $assignmentsBySubProjectsAndAnnotatorsQuery,
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
     * @param  array<int, mixed>  $annotatorIds
     *
     * @return array<string, mixed>
     */
    private function preloadData(array $annotatorIds): array {
        $annotatorProjectLinks = $this->annotatorProjectLinksQuery->get($annotatorIds);

        $projectIds = $annotatorProjectLinks->pluck('project_id')->unique()->all();

        /** @var Collection<int, Project> $projects */
        $projects = $this->projectsByIdsQuery->get($projectIds, ProjectStatusEnum::IN_PROGRESS);

        $loadedProjectIds = $projects->pluck('id')->all();

        /** @var Collection<int, SubProject> $subProjects */
        $subProjects = $this->subProjectsByProjectsQuery->get($loadedProjectIds, ProjectStatusEnum::IN_PROGRESS);

        $subProjectIds = $subProjects->pluck('id')->all();

        $assignments = $this->assignmentsBySubProjectsAndAnnotatorsQuery->get($subProjectIds, $annotatorIds);

        return [
            'annotator_project_links' => $annotatorProjectLinks,
            'projects_by_id' => $projects->keyBy('id'),
            'subprojects_by_project' => $subProjects->groupBy('project_id'),
            'assignments_by_annotator' => $assignments->groupBy('user_id'),
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

        /** @var SupportCollection<int|string, Collection<int, AnnotatorOfProject>> $linksByAnnotator */
        $linksByAnnotator = $annotatorProjectLinks->groupBy('user_id');

        /** @var array<int, int> $annotatorIds */
        $annotatorIds = $annotators->pluck('id')->all();
        $workloadsByAnnotator = $this->workloadService->computeNormalizedWorkloads($annotatorIds);

        $result = [];

        foreach ($annotators as $annotator) {
            /** @var Collection<int, AnnotatorOfProject> $myLinks */
            $myLinks = $linksByAnnotator->get($annotator->id) ?? new Collection();
            $myProjectIds = $myLinks->pluck('project_id')->all();

            /** @var Collection<int, AnnotationAssignment> $myAssignments */
            $myAssignments = $assignmentsByAnnotator->get($annotator->id) ?? new Collection();
            /** @var array<int, int> $mySubProjectIds */
            $mySubProjectIds = $myAssignments->pluck('sub_project_id')->all();

            /** @var Collection<int, Project> $myProjects */
            $myProjects = $projectsById->filter(
                fn (Project $p): bool => in_array($p->id, $myProjectIds, true)
            );

            $progressBySubProject = $this->subProjectService->getProgress($mySubProjectIds);

            $totalAll = 0;
            $totalDone = 0;
            foreach ($progressBySubProject as $spProgress) {
                foreach ($spProgress['assignments'] as $assignment) {
                    $totalAll += $assignment['annotations_all'];
                    $totalDone += $assignment['annotations_done'];
                }
            }

            $annotatorProgress = $totalAll > 0 ? (float) ($totalDone / $totalAll) : 0.0;

            $projectsData = [];
            $hiddenProjectsData = [];

            foreach ($myProjects as $project) {
                /** @var Collection<int, SubProject> $allProjectSubProjects */
                $allProjectSubProjects = $subProjectsByProject->get($project->id) ?? new Collection();

                /** @var Collection<int, SubProject> $annotatorSubProjects */
                $annotatorSubProjects = $allProjectSubProjects->filter(
                    fn (SubProject $sp): bool => in_array($sp->id, $mySubProjectIds, true)
                );

                if ($project->restricted_visibility) {
                    $hiddenProjectsData[] = $this->formatHiddenProject($project, $annotatorSubProjects->count());
                } else {
                    $projectsData[] = $this->formatProject(
                        $project,
                        $annotatorSubProjects,
                        $workloadsByAnnotator[$annotator->id]['per_subproject'] ?? [],
                        $progressBySubProject,
                    );
                }
            }

            $result[] = [
                'id' => $annotator->id,
                'username' => $annotator->username,
                'status' => $annotator->status === StatusEnum::ACTIVE,
                'active_subprojects' => count($mySubProjectIds),
                'active_projects' => $myProjects->count(),
                'workload' => $workloadsByAnnotator[$annotator->id]['total'] ?? 0.5,
                'progress' => $annotatorProgress,
                'projects' => $projectsData,
                'hidden_projects' => $hiddenProjectsData,
            ];
        }

        return $result;
    }

    /**
     * @param  Collection<int, SubProject>  $subProjects
     * @param  array<int, float>  $subprojectWorkloads  Normalized workload keyed by sub_project_id
     * @param  array<int, array{progress: float, assignments: array<int, array{user_id: int, annotations_all: int, annotations_done: int, progress: float}>}>  $progressBySubProject
     *
     * @return array<string, mixed>
     */
    private function formatProject(Project $project, Collection $subProjects, array $subprojectWorkloads, array $progressBySubProject): array {
        $ownerId = $project->owner_user_id;

        $coManagers = $project->projectManagers
            ->filter(fn (ProjectManager $pm): bool => $pm->user_id !== $ownerId)
            ->map(fn (ProjectManager $pm): array => ['id' => $pm->user->id, 'username' => $pm->user->username])
            ->values()
            ->all();

        $projectTotalAll = 0;
        $projectTotalDone = 0;
        foreach ($subProjects as $sp) {
            foreach (($progressBySubProject[$sp->id]['assignments'] ?? []) as $assignment) {
                $projectTotalAll += $assignment['annotations_all'];
                $projectTotalDone += $assignment['annotations_done'];
            }
        }

        $projectProgress = $projectTotalAll > 0 ? (float) ($projectTotalDone / $projectTotalAll) : 0.0;

        return [
            'id' => $project->id,
            'name' => $project->name,
            'status' => $project->status,
            'annotation_task_title' => $project->annotationTask->title,
            'dataset_name' => $project->dataset->name,
            'owner_name' => $project->owner->username,
            'co_managers' => $coManagers,
            'project_progress' => $projectProgress,
            'notifications_count' => 0,
            'started_at' => $project->started_at,
            'completed_at' => $project->completed_at,
            'scheduled_at' => $project->scheduled_at,
            'deadline_at' => $project->deadline_at,
            'is_delayed_to_start' => $project->isDelayedToStart(),
            'is_delayed_to_end' => $project->isDelayedToEnd(),
            'subprojects' => $subProjects
                ->map(fn (SubProject $sp): array => [
                    'id' => $sp->id,
                    'name' => $sp->name,
                    'status' => $sp->status,
                    'workload' => $subprojectWorkloads[$sp->id] ?? 0.5,
                    'progress' => $progressBySubProject[$sp->id]['progress'] ?? 0.0,
                    'started_at' => $sp->started_at,
                    'completed_at' => $sp->completed_at,
                    'scheduled_at' => $sp->scheduled_at,
                    'deadline_at' => $sp->deadline_at,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatHiddenProject(Project $project, int $activeSubprojectsCount): array {
        return [
            'owner_name' => $project->owner->username,
            'active_subprojects_count' => $activeSubprojectsCount,
        ];
    }
}
