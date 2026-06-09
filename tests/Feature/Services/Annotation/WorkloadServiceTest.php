<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Models\AnnotationAssignment;
use App\Models\AnnotationTask;
use App\Models\Dataset;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use App\Services\Annotation\WorkloadService;
use Illuminate\Support\Facades\DB;

describe('WorkloadService::computeNormalizedWorkloads', function (): void {
    beforeEach(function (): void {
        // Two annotation tasks with distinct weights.
        $this->taskA = AnnotationTask::factory()->create(['title' => 'Task A', 'short_description' => 'A', 'weight' => 3]);
        $this->taskB = AnnotationTask::factory()->create(['title' => 'Task B', 'short_description' => 'B', 'weight' => 5]);

        // Four datasets whose instance counts cover the sentinel boundary cases:
        // smallest (10) < FLOOR (100), largest (10000) > CEILING (1000).
        $now = now()->toDateTimeString();
        $this->datasets = collect();

        foreach ([10, 100, 1000, 10000] as $count) {
            $dataset = Dataset::factory()->create();

            $rows = [];
            for ($i = 0; $i < $count; $i++) {
                $rows[] = [
                    'index' => $i,
                    'dataset_id' => $dataset->id,
                    'content' => '{}',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('dataset_instances')->insert($chunk);
            }

            $dataset->update(['size' => $count]);
            $this->datasets->push($dataset);
        }

        // Five annotators shared across all projects.
        $this->annotators = User::factory()->count(5)->create();
        $owner = User::factory()->create();

        // Ten projects with subproject counts [1,2,3,4,5,1,2,3,4,5] = 30 subprojects total.
        // All five annotators are assigned to every subproject via AnnotationAssignment.
        $this->allSubProjects = collect();
        $subprojectCounts = [1, 2, 3, 4, 5, 1, 2, 3, 4, 5];

        foreach ($subprojectCounts as $idx => $spCount) {
            $project = Project::factory()->create([
                'owner_user_id' => $owner->id,
                'annotation_task_id' => ($idx % 2 === 0 ? $this->taskA : $this->taskB)->id,
                'dataset_id' => $this->datasets[$idx % 4]->id,
                'status' => ProjectStatusEnum::IN_PROGRESS,
            ]);

            for ($j = 0; $j < $spCount; $j++) {
                // Vary last_instance_index so effort differs across subprojects: 20, 40, 60, 80, 100.
                $subProject = SubProject::factory()->create([
                    'project_id' => $project->id,
                    'status' => ProjectStatusEnum::IN_PROGRESS,
                    'first_instance_index' => 1,
                    'last_instance_index' => ($j + 1) * 20,
                ]);

                foreach ($this->annotators as $annotator) {
                    AnnotationAssignment::factory()->create([
                        'user_id' => $annotator->id,
                        'sub_project_id' => $subProject->id,
                    ]);
                }

                $this->allSubProjects->push($subProject);
            }
        }

        $this->service = resolve(WorkloadService::class);
    });

    // --- basic contract ---

    it('returns an empty array when given no annotator IDs', function (): void {
        expect($this->service->computeNormalizedWorkloads([]))->toBe([]);
    });

    it('returns an entry keyed by every annotator ID passed', function (): void {
        $ids = $this->annotators->pluck('id')->all();

        $result = $this->service->computeNormalizedWorkloads($ids);

        expect($result)
            ->toHaveCount(5)
            ->and(array_keys($result))->toEqual($ids);
    });

    it('each entry contains total and per_subproject keys', function (): void {
        $ids = $this->annotators->pluck('id')->all();

        $result = $this->service->computeNormalizedWorkloads($ids);

        foreach ($result as $entry) {
            expect($entry)->toHaveKeys(['total', 'per_subproject']);
        }
    });

    // --- range guarantees ---

    it('normalizes total workload to [0.1, 0.9]', function (): void {
        $ids = $this->annotators->pluck('id')->all();

        $result = $this->service->computeNormalizedWorkloads($ids);

        foreach ($result as $entry) {
            expect($entry['total'])
                ->toBeFloat()
                ->toBeGreaterThanOrEqual(0.1)
                ->toBeLessThanOrEqual(0.9);
        }
    });

    it('normalizes per_subproject workload to [0.1, 0.9]', function (): void {
        $ids = $this->annotators->pluck('id')->all();

        $result = $this->service->computeNormalizedWorkloads($ids);

        foreach ($result as $entry) {
            foreach ($entry['per_subproject'] as $spWorkload) {
                expect($spWorkload)
                    ->toBeFloat()
                    ->toBeGreaterThanOrEqual(0.1)
                    ->toBeLessThanOrEqual(0.9);
            }
        }
    });

    // --- per_subproject structure ---

    it('per_subproject contains exactly the subproject IDs assigned to that annotator', function (): void {
        $annotator = $this->annotators->first();
        $expectedIds = $this->allSubProjects->pluck('id')->sort()->values()->all();

        $result = $this->service->computeNormalizedWorkloads([$annotator->id]);

        $actualIds = array_keys($result[$annotator->id]['per_subproject']);
        sort($actualIds);

        expect($actualIds)->toEqual($expectedIds);
    });

    it('per_subproject is empty for an annotator with no active subprojects', function (): void {
        $outsider = User::factory()->create();

        $result = $this->service->computeNormalizedWorkloads([$outsider->id]);

        expect($result[$outsider->id]['per_subproject'])->toBe([]);
    });

    // --- workload ordering ---

    it('all totals are 0.9 when every annotator has identical positive remaining work', function (): void {
        // No annotations created → all annotators have the same positive raw effort.
        // With hybrid normalization the floor sentinel (0) forces range > 0, so all
        // identically-loaded annotators end up at the maximum and normalize to 0.9.
        $ids = $this->annotators->pluck('id')->all();

        $result = $this->service->computeNormalizedWorkloads($ids);

        foreach ($result as $entry) {
            expect($entry['total'])->toBe(0.9);
        }
    });

    it('annotator who completed all work has a lower total than one who did none', function (): void {
        $fullyDone = $this->annotators->first();
        $noWorkDone = $this->annotators->last();

        // Grab enough distinct dataset_instance IDs for the largest subproject (100 instances).
        // The unique constraint is (annotation_assignment_id, dataset_instance_id), so the same
        // instance ID may be reused across different assignments.
        $instanceIds = DB::table('dataset_instances')->limit(100)->pluck('id')->all();
        $now = now()->toDateTimeString();

        $assignments = AnnotationAssignment::query()
            ->where('user_id', $fullyDone->id)
            ->get();

        foreach ($assignments as $assignment) {
            /** @var SubProject $sp */
            $sp = $this->allSubProjects->firstWhere('id', $assignment->sub_project_id);
            $count = $sp->last_instance_index - $sp->first_instance_index;

            $rows = [];
            for ($i = 0; $i < $count; $i++) {
                $rows[] = [
                    'annotation_assignment_id' => $assignment->id,
                    'dataset_instance_id' => $instanceIds[$i],
                    'project_instance_index' => $i,
                    'annotator_instance_index' => $i,
                    'annotations' => '[]',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('annotations')->insert($rows);
        }

        $result = $this->service->computeNormalizedWorkloads([$fullyDone->id, $noWorkDone->id]);

        expect($result[$fullyDone->id]['total'])
            ->toBeLessThan($result[$noWorkDone->id]['total']);
    });

    it('does not count unannotated rows (annotations=null) as completed work', function (): void {
        // Arrange: insert rows where annotations=null and pending=false — the buggy
        // filter (where pending=false) matched these and inflated workDone.
        $annotator = $this->annotators->first();
        $noRows = $this->annotators->last();
        $sp = $this->allSubProjects->first();
        $assignment = AnnotationAssignment::query()
            ->where('user_id', $annotator->id)
            ->where('sub_project_id', $sp->id)
            ->first();

        $instanceIds = DB::table('dataset_instances')->limit(20)->pluck('id')->all();
        $now = now()->toDateTimeString();

        $rows = [];
        for ($i = 0; $i < 20; $i++) {
            $rows[] = [
                'annotation_assignment_id' => $assignment->id,
                'dataset_instance_id' => $instanceIds[$i],
                'project_instance_index' => $i + 1,
                'annotator_instance_index' => $i + 1,
                'annotations' => null,
                'pending' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('annotations')->insert($rows);

        // Act
        $result = $this->service->computeNormalizedWorkloads([$annotator->id, $noRows->id]);

        // Assert: all-null rows == no rows; neither has done any real work
        expect($result[$annotator->id]['total'])->toBe($result[$noRows->id]['total']);
    });

    it('counts a pending annotation as half a unit of work done', function (): void {
        $fullyPending = $this->annotators->first();
        $fullyDone = $this->annotators->get(1);
        $noWorkDone = $this->annotators->last();

        // Isolate to one subproject so the math is clean and SP effort differences
        // from the other 29 subprojects do not dwarf the pending-weight signal.
        $sp = $this->allSubProjects->first(); // 20 instances
        $pendingAssignment = AnnotationAssignment::query()
            ->where('user_id', $fullyPending->id)->where('sub_project_id', $sp->id)->first();
        $doneAssignment = AnnotationAssignment::query()
            ->where('user_id', $fullyDone->id)->where('sub_project_id', $sp->id)->first();

        $instanceIds = DB::table('dataset_instances')->limit(20)->pluck('id')->all();
        $now = now()->toDateTimeString();

        $doneRows = [];
        for ($i = 0; $i < 20; $i++) {
            $doneRows[] = [
                'annotation_assignment_id' => $doneAssignment->id,
                'dataset_instance_id' => $instanceIds[$i],
                'project_instance_index' => $i + 1,
                'annotator_instance_index' => $i + 1,
                'annotations' => '[]',
                'pending' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('annotations')->insert($doneRows);

        $pendingRows = [];
        for ($i = 0; $i < 20; $i++) {
            $pendingRows[] = [
                'annotation_assignment_id' => $pendingAssignment->id,
                'dataset_instance_id' => $instanceIds[$i],
                'project_instance_index' => $i + 1,
                'annotator_instance_index' => $i + 1,
                'annotations' => '[]',
                'pending' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('annotations')->insert($pendingRows);

        // Act — restrict to this one subproject so effort is 20 instances for all three.
        // fullyDone:    workDone=all, raw=0  → 0.1
        // fullyPending: workDone=half, raw=half → 0.5
        // noWorkDone:   workDone=0,   raw=max → 0.9
        $result = $this->service->computeNormalizedWorkloads(
            [$fullyDone->id, $fullyPending->id, $noWorkDone->id],
            [$sp->id],
        );

        // Assert: fullyDone < fullyPending < noWorkDone
        expect($result[$fullyDone->id]['total'])
            ->toBeLessThan($result[$fullyPending->id]['total'])
            ->and($result[$fullyPending->id]['total'])
            ->toBeLessThan($result[$noWorkDone->id]['total']);
    });

    it('subproject with more remaining work has a higher per_subproject workload', function (): void {
        // All annotators start with 0 annotations done. Pick one annotator and compare
        // a light subproject (20 instances) against a heavy one (100 instances) —
        // same annotation task weight, so effort scales linearly with instance count.
        $annotator = $this->annotators->first();

        // Find the lightest and heaviest subprojects for this annotator.
        $light = $this->allSubProjects->sortBy('last_instance_index')->first();
        $heavy = $this->allSubProjects->sortByDesc('last_instance_index')->first();

        // Ensure they belong to projects with the same annotation task so weight is equal.
        // Grab any two subprojects from projects that use taskA (weight=3) to isolate instance count.
        $taskASubProjects = $this->allSubProjects->filter(fn (SubProject $sp): bool => $sp->project->annotation_task_id === $this->taskA->id)->sortBy('last_instance_index')->values();

        if ($taskASubProjects->count() < 2) {
            $this->markTestSkipped('Not enough same-task subprojects to compare.');
        }

        $light = $taskASubProjects->first();
        $heavy = $taskASubProjects->last();

        $result = $this->service->computeNormalizedWorkloads([$annotator->id]);
        $perSp = $result[$annotator->id]['per_subproject'];

        expect($perSp[$heavy->id])->toBeGreaterThanOrEqual($perSp[$light->id]);
    });
});
