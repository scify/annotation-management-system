<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Enums\ProjectStatusEnum;
use App\Exceptions\AnnotatorDetachException;
use App\Exceptions\InvalidProjectStatusTransitionException;
use App\Exceptions\ProjectOwnershipException;
use App\Models\Project;
use App\Models\User;
use App\Queries\Annotator\GetAnnotatorProjectLinksByProjectQuery;
use App\Queries\Manager\ConnectManagerToAnnotatorsQuery;
use App\Queries\Project\AttachAnnotatorsToProjectQuery;
use App\Queries\Project\CompleteSubProjectsByProjectQuery;
use App\Queries\Project\DeleteAnnotationsByProjectQuery;
use App\Queries\Project\DetachAnnotatorFromProjectQuery;
use App\Queries\Project\GetSubProjectIdsQuery;
use App\Queries\Project\ProposeOwnershipTransferQuery;
use App\Queries\Project\StoreInstanceShuffleMappersQuery;
use App\Queries\Project\StoreProjectManagerQuery;
use App\Queries\Project\StoreProjectQuery;
use App\Queries\Project\UpdateProjectStatusQuery;
use App\Queries\SubProject\GetAssignmentsBySubProjectsAndAnnotatorsQuery;
use App\Services\Dataset\DatasetService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class ProjectWriteService {
    public function __construct(
        private DatasetService $datasetService,
        private GetAnnotatorProjectLinksByProjectQuery $annotatorProjectLinksQuery,
        private GetAssignmentsBySubProjectsAndAnnotatorsQuery $assignmentsBySubProjectsAndAnnotatorsQuery,
        private GetSubProjectIdsQuery $subProjectIdsQuery,
        private ConnectManagerToAnnotatorsQuery $connectManagerToAnnotatorsQuery,
        private AttachAnnotatorsToProjectQuery $attachAnnotatorsToProjectQuery,
        private CompleteSubProjectsByProjectQuery $completeSubProjectsByProjectQuery,
        private DeleteAnnotationsByProjectQuery $deleteAnnotationsByProjectQuery,
        private DetachAnnotatorFromProjectQuery $detachAnnotatorFromProjectQuery,
        private ProposeOwnershipTransferQuery $proposeOwnershipTransferQuery,
        private StoreInstanceShuffleMappersQuery $storeInstanceShuffleMappersQuery,
        private StoreProjectManagerQuery $storeProjectManagerQuery,
        private StoreProjectQuery $storeProjectQuery,
        private UpdateProjectStatusQuery $updateProjectStatusQuery,
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

            $project = $this->storeProjectQuery->execute($data, $owner->id);

            $this->storeProjectManagerQuery->create($project->id, $owner->id);

            foreach ($coManagerIds as $managerId) {
                $this->storeProjectManagerQuery->firstOrCreate($project->id, $managerId);
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

                $this->storeInstanceShuffleMappersQuery->insert($rows);
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
        $subProjectIds = $this->subProjectIdsQuery->get([$projectId])->all();

        $hasAssignments = $this->assignmentsBySubProjectsAndAnnotatorsQuery
            ->existsBySubProjectsAndAnnotator($subProjectIds, $annotatorId);

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

    public function proposeOwnershipTransfer(int $projectId, int $userId): void {
        if ($this->proposeOwnershipTransferQuery->hasActiveProposal($projectId)) {
            throw ProjectOwnershipException::ownershipAlreadyProposed();
        }

        $this->proposeOwnershipTransferQuery->execute($projectId, $userId);
    }

    /**
     * Assign all annotators of a project to a (co-)manager.
     * Called when a co-manager accepts a project invitation.
     *
     * TODO: wire this call into the invitation acceptance handler once implemented.
     */
    public function assignAnnotatorsToManagers(int $projectId, int $managerId): void {
        $annotatorIds = $this->annotatorProjectLinksQuery->getUserIds($projectId);

        if ($annotatorIds === []) {
            return;
        }

        $this->connectManagerToAnnotatorsQuery->bulkConnect($managerId, $annotatorIds);
    }
}
