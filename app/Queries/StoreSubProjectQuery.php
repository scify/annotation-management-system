<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\DatasetInstance;
use App\Models\InstanceShuffleMapper;
use App\Models\Project;
use App\Models\SubProject;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class StoreSubProjectQuery {
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function execute(int $projectId, array $data): void {
        DB::transaction(function () use ($projectId, $data): void {
            /** @var array<int, int> $annotatorIds */
            $annotatorIds = $data['annotator_ids'];

            $subProject = SubProject::query()->create([
                'project_id' => $projectId,
                'name' => $data['name'],
                'priority' => $data['priority'],
                'flexible' => $data['is_flexible'],
                'auto_submission' => ! $data['is_flexible'] || ! $data['requires_confirmation'],
                'minimum_annotators' => $data['minimum_annotations'] ?? count($annotatorIds),
                'first_instance_index' => $data['from_instance'],
                'last_instance_index' => $data['to_instance'],
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'deadline_at' => $data['deadline_at'] ?? null,
            ]);

            $now = now();
            $assignmentRows = [];

            foreach ($annotatorIds as $annotatorId) {
                $assignmentRows[] = [
                    'user_id' => $annotatorId,
                    'sub_project_id' => $subProject->id,
                    'is_instance_shuffled' => $data['shuffle'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            AnnotationAssignment::query()->insert($assignmentRows);

            /** @var array<int, int> $assignmentIds */
            $assignmentIds = AnnotationAssignment::query()
                ->where('sub_project_id', $subProject->id)
                ->pluck('id')
                ->all();

            $project = Project::query()
                ->select(['id', 'dataset_id', 'is_instance_shuffled'])
                ->findOrFail($projectId);

            /** @var int $firstIndex */
            $firstIndex = $data['from_instance'];
            /** @var int $lastIndex */
            $lastIndex = $data['to_instance'];

            $instanceIdByIndex = $this->resolveDatasetInstanceIds(
                $projectId,
                $project->dataset_id,
                $project->is_instance_shuffled,
                $firstIndex,
                $lastIndex,
            );

            // TODO: when $data['shuffle'] is true, each annotator's instance order should be
            // individually randomised before building annotation rows (different permutation per assignment).
            $annotationRows = [];

            foreach ($assignmentIds as $assignmentId) {
                for ($index = $firstIndex; $index <= $lastIndex; $index++) {
                    $annotationRows[] = [
                        'annotation_assignment_id' => $assignmentId,
                        'dataset_instance_id' => $instanceIdByIndex[$index],
                        'index' => $index,
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
        bool $is_instance_shuffled,
        int $from,
        int $to,
    ): array {
        if ($is_instance_shuffled) {
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
