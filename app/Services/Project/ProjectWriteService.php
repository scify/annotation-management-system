<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Enums\ProjectStatusEnum;
use App\Exceptions\AnnotatorDetachException;
use App\Exceptions\InvalidProjectStatusTransitionException;
use App\Models\AnnotationAssignment;
use App\Models\AnnotatorOfProject;
use App\Models\InstanceShuffleMapper;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\SubProject;
use App\Models\User;
use App\Queries\Project\AttachAnnotatorsToProjectQuery;
use App\Queries\Project\CompleteSubProjectsByProjectQuery;
use App\Queries\Project\DeleteAnnotationsByProjectQuery;
use App\Queries\Project\DetachAnnotatorFromProjectQuery;
use App\Queries\Project\UpdateProjectStatusQuery;
use App\Services\Dataset\DatasetService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class ProjectWriteService {
    public function __construct(
        private DatasetService $datasetService,
        private AttachAnnotatorsToProjectQuery $attachAnnotatorsToProjectQuery,
        private DetachAnnotatorFromProjectQuery $detachAnnotatorFromProjectQuery,
        private UpdateProjectStatusQuery $updateProjectStatusQuery,
        private CompleteSubProjectsByProjectQuery $completeSubProjectsByProjectQuery,
        private DeleteAnnotationsByProjectQuery $deleteAnnotationsByProjectQuery,
    ) {}

    /**
     * Creates a project with its manager assignments and annotator snapshot.
     *
     * @param  array<string, mixed>  $data  Validated data from ProjectStoreRequest
     *
     * @throws Throwable
     */
    public function storeProject(User $owner, array $data): Project {
        return DB::transaction(function () use ($owner, $data): Project {
            /** @var array<int, int> $annotatorIds */
            $annotatorIds = $data['annotator_ids'];
            /** @var array<int, int> $coManagerIds */
            $coManagerIds = $data['co_manager_ids'] ?? [];

            $project = Project::query()->create([
                'name' => $data['name'],
                'owner_user_id' => $owner->id,
                'annotation_task_id' => $data['annotation_task_id'],
                'dataset_id' => $data['dataset_id'],
                'status' => ProjectStatusEnum::PENDING,
                'is_instance_shuffled' => $data['is_instance_shuffled'],
                'annotation_task_configuration' => $data['annotation_task_configuration'] ?? null,
                'restricted_visibility' => $data['restricted_visibility'],
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'deadline_at' => $data['deadline_at'] ?? null,
            ]);

            ProjectManager::query()->create([
                'project_id' => $project->id,
                'user_id' => $owner->id,
                'accepted' => true,
            ]);

            foreach ($coManagerIds as $managerId) {
                ProjectManager::query()->firstOrCreate(
                    ['project_id' => $project->id, 'user_id' => $managerId],
                    ['accepted' => true],
                );
            }

            if ($project->is_instance_shuffled) {
                $shuffled = $this->datasetService->generateShuffledIndexArray($project->dataset_id);
                $now = now();
                $rows = [];
                foreach ($shuffled as $newIndex => $oldIndex) {
                    $rows[] = [
                        'project_id' => $project->id,
                        'new_index' => $newIndex + 1,
                        'old_index' => $oldIndex,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                InstanceShuffleMapper::query()->insert($rows);
            }

            $project->annotators()->sync($annotatorIds);

            return $project;
        });
    }

    /**
     * @param  array<int, int>  $annotatorIds
     */
    public function attachAnnotators(int $projectId, array $annotatorIds): void {
        $this->attachAnnotatorsToProjectQuery->attach($projectId, $annotatorIds);
    }

    public function detachAnnotator(int $projectId, int $annotatorId): void {
        $subProjectIds = SubProject::query()
            ->where('project_id', $projectId)
            ->pluck('id')
            ->all();

        $hasAssignments = AnnotationAssignment::query()
            ->whereIn('sub_project_id', $subProjectIds)
            ->where('user_id', $annotatorId)
            ->exists();

        if ($hasAssignments) {
            throw AnnotatorDetachException::annotatorHasSubProjectAssignments();
        }

        $this->detachAnnotatorFromProjectQuery->detach($projectId, $annotatorId);
    }

    public function changeStatus(Project $project, ProjectStatusEnum $newStatus): Project {
        $allowed = match ($project->status) {
            ProjectStatusEnum::PENDING => ProjectStatusEnum::IN_PROGRESS,
            ProjectStatusEnum::IN_PROGRESS => ProjectStatusEnum::COMPLETED,
            ProjectStatusEnum::COMPLETED => null,
        };

        if ($allowed !== $newStatus) {
            throw new InvalidProjectStatusTransitionException($project->status, $newStatus);
        }

        $this->updateProjectStatusQuery->execute($project, $newStatus);

        if ($newStatus === ProjectStatusEnum::COMPLETED) {
            $this->completeSubProjectsByProjectQuery->execute($project->id);
        }

        return $project;
    }

    public function deleteProject(Project $project): void {
        $this->deleteAnnotationsByProjectQuery->execute($project->id);
        $project->delete();
    }

    /**
     * Assign all annotators of a project to a (co-)manager.
     * Called when a co-manager accepts a project invitation.
     *
     * TODO: wire this call into the invitation acceptance handler once implemented.
     */
    public function assignAnnotatorsToManagers(int $projectId, int $managerId): void {
        $annotatorIds = AnnotatorOfProject::query()
            ->where('project_id', $projectId)
            ->pluck('user_id')
            ->all();

        if ($annotatorIds === []) {
            return;
        }

        $now = now();
        $rows = [];
        foreach ($annotatorIds as $annotatorId) {
            $rows[] = [
                'manager_id' => $managerId,
                'annotator_id' => $annotatorId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('annotator_of_managers')->insertOrIgnore($rows);
    }
}
