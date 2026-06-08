<?php

declare(strict_types=1);

namespace App\Queries\SubProject;

use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\DatasetInstance;
use App\Models\InstanceShuffleMapper;
use App\Models\SubProject;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class AttachAnnotatorsToSubProjectQuery {
    /**
     * @param  array<int, int>  $annotatorIds
     */
    public function attach(SubProject $subProject, array $annotatorIds): void {
        DB::transaction(function () use ($subProject, $annotatorIds): void {
            /** @var array<int, int> $existingUserIds */
            $existingUserIds = AnnotationAssignment::query()
                ->where('sub_project_id', $subProject->id)
                ->whereIn('user_id', $annotatorIds)
                ->pluck('user_id')
                ->all();

            /** @var array<int, int> $newAnnotatorIds */
            $newAnnotatorIds = array_values(array_diff($annotatorIds, $existingUserIds));

            if ($newAnnotatorIds === []) {
                return;
            }

            $isInstanceShuffled = (bool) (AnnotationAssignment::query()
                ->where('sub_project_id', $subProject->id)
                ->value('is_instance_shuffled') ?? false);

            $now = now();

            $assignmentRows = array_map(fn (int $annotatorId): array => [
                'user_id' => $annotatorId,
                'sub_project_id' => $subProject->id,
                'is_instance_shuffled' => $isInstanceShuffled,
                'created_at' => $now,
                'updated_at' => $now,
            ], $newAnnotatorIds);

            AnnotationAssignment::query()->insert($assignmentRows);

            /** @var array<int, int> $newAssignmentIds */
            $newAssignmentIds = AnnotationAssignment::query()
                ->where('sub_project_id', $subProject->id)
                ->whereIn('user_id', $newAnnotatorIds)
                ->pluck('id')
                ->all();

            $project = $subProject->project;
            $firstIndex = $subProject->first_instance_index;
            $lastIndex = $subProject->last_instance_index;

            $instanceIdByIndex = $this->resolveDatasetInstanceIds(
                $project->id,
                $project->dataset_id,
                $project->is_instance_shuffled,
                $firstIndex,
                $lastIndex,
            );

            $projectIndices = range($firstIndex, $lastIndex);
            $annotationRows = [];

            foreach ($newAssignmentIds as $assignmentId) {
                $orderedProjectIndices = $isInstanceShuffled
                    ? Arr::shuffle($projectIndices)
                    : $projectIndices;

                foreach ($orderedProjectIndices as $annotatorPos => $projectIndex) {
                    $annotationRows[] = [
                        'annotation_assignment_id' => $assignmentId,
                        'dataset_instance_id' => $instanceIdByIndex[$projectIndex],
                        'project_instance_index' => $projectIndex,
                        'annotator_instance_index' => $firstIndex + $annotatorPos,
                        'annotations' => null,
                        'pending' => false,
                        'is_flagged' => false,
                        'confidence' => null,
                        'last_edited_by' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            Annotation::query()->insert($annotationRows);
        });
    }

    /**
     * Returns a display-index → dataset_instance_id map for the given range.
     * For shuffled projects, translates new_index → old_index via instance_shuffle_mappers
     * before resolving against dataset_instances.
     *
     * @return array<int, int>
     */
    private function resolveDatasetInstanceIds(
        int $projectId,
        int $datasetId,
        bool $isInstanceShuffled,
        int $from,
        int $to,
    ): array {
        if ($isInstanceShuffled) {
            /** @var array<int, int> $shuffleMap */
            $shuffleMap = InstanceShuffleMapper::query()
                ->where('project_id', $projectId)
                ->whereBetween('new_index', [$from, $to])
                ->pluck('old_index', 'new_index')
                ->all();

            /** @var array<int, int> $instanceIds */
            $instanceIds = DatasetInstance::query()
                ->where('dataset_id', $datasetId)
                ->whereIn('index', array_values($shuffleMap))
                ->pluck('id', 'index')
                ->all();

            return array_map(fn (int $oldIndex) => $instanceIds[$oldIndex], $shuffleMap);
        }

        /** @var array<int, int> $result */
        $result = DatasetInstance::query()
            ->where('dataset_id', $datasetId)
            ->whereBetween('index', [$from, $to])
            ->pluck('id', 'index')
            ->all();

        return $result;
    }
}
