<?php

declare(strict_types=1);

namespace Database\Seeders\Dummy;

use App\Enums\ConfidenceEnum;
use App\Enums\NotificationThreadTypeEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\SubProjectPriorityEnum;
use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\AnnotationTask;
use App\Models\AnnotatorOfManager;
use App\Models\Dataset;
use App\Models\DatasetInstance;
use App\Models\InstanceShuffleMapper;
use App\Models\NotificationThread;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\SubProject;
use App\Models\User;
use App\Services\Dataset\DatasetService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Random\RandomException;

class DummyProjectSeeder extends Seeder {
    /**
     * @throws RandomException
     */
    public function run(): void {
        $managerCarol = User::query()->where('email', 'manager.carol@example.com')->firstOrFail();
        $managerDave = User::query()->where('email', 'manager.dave@example.com')->firstOrFail();
        $adminAlice = User::query()->where('email', 'admin.alice@example.com')->firstOrFail();
        $adminBob = User::query()->where('email', 'admin.bob@example.com')->firstOrFail();

        $newsEN = Dataset::query()->where('name', 'News Articles EN')->firstOrFail();
        $newsEL = Dataset::query()->where('name', 'News Articles EL')->firstOrFail();
        $productReviews = Dataset::query()->where('name', 'Product Reviews')->firstOrFail();
        $socialMedia = Dataset::query()->where('name', 'Social Media Posts')->firstOrFail();

        $nerTask = AnnotationTask::query()->where('title', 'Named Entity Recognition')->firstOrFail();
        $sentimentTask = AnnotationTask::query()->where('title', 'Sentiment Analysis')->firstOrFail();
        $toxicTask = AnnotationTask::query()->where('title', 'Toxic Content Detection')->firstOrFail();

        $projects = [
            [
                'manager_ids' => [$adminAlice->getKey(), $managerDave->getKey()],
                'annotator_emails' => ['annotator.eva@example.com', 'annotator.grace@example.com', 'annotator.frank@example.com'],
                'project' => [
                    'name' => 'NER – English News',

                    'owner_user_id' => $managerCarol->getKey(),
                    'annotation_task_id' => $nerTask->getKey(),
                    'dataset_id' => $newsEN->getKey(),
                    'status' => ProjectStatusEnum::IN_PROGRESS,
                    'restricted_visibility' => false,
                    'is_instance_shuffled' => false,
                    'scheduled_at' => '2026-03-01',
                    'started_at' => '2026-04-01 09:00:00',
                    'deadline_at' => '2026-06-30',
                    'annotation_task_configuration' => [
                        ['id' => 0, 'answer' => 'Yes'],
                    ],
                ],
                'subprojects' => [
                    [
                        'name' => 'Batch 1',
                        'status' => ProjectStatusEnum::IN_PROGRESS,
                        'priority' => SubProjectPriorityEnum::MEDIUM,
                        'flexible' => false,
                        'auto_submission' => true,
                        'minimum_annotators' => 1,
                        'first_instance_index' => 1,
                        'last_instance_index' => 200,
                        'scheduled_at' => '2026-03-01',
                        'started_at' => '2026-04-01 09:00:00',
                        'deadline_at' => '2026-05-31',
                        'annotators' => ['annotator.eva@example.com', 'annotator.grace@example.com'],
                        'shuffle' => true,
                    ],
                    [
                        'name' => 'Batch 2',
                        'status' => ProjectStatusEnum::PENDING,
                        'priority' => SubProjectPriorityEnum::MEDIUM,
                        'flexible' => false,
                        'auto_submission' => true,
                        'minimum_annotators' => 1,
                        'first_instance_index' => 201,
                        'last_instance_index' => 500,
                        'scheduled_at' => '2026-05-01',
                        'deadline_at' => '2026-06-30',
                        'annotators' => ['annotator.frank@example.com', 'annotator.henry@example.com'],
                    ],
                    [
                        'name' => 'Batch 3',
                        'status' => ProjectStatusEnum::IN_PROGRESS,
                        'priority' => SubProjectPriorityEnum::HIGH,
                        'flexible' => false,
                        'auto_submission' => false,
                        'minimum_annotators' => 1,
                        'first_instance_index' => 1,
                        'last_instance_index' => 10,
                        'scheduled_at' => '2026-03-01',
                        'started_at' => '2026-04-01 09:00:00',
                        'deadline_at' => '2026-06-30',
                        'submitted_count' => 2,
                        'pending_count' => 3,
                        'annotators' => ['annotator.eva@example.com'],
                    ],
                ],
            ],
            [
                'manager_ids' => [$adminBob->getKey()],
                'annotator_emails' => ['annotator.ivy@example.com', 'annotator.jack@example.com', 'annotator.karen@example.com'],
                'project' => [
                    'name' => 'NER – Greek News',
                    'owner_user_id' => $managerDave->getKey(),
                    'annotation_task_id' => $nerTask->getKey(),
                    'dataset_id' => $newsEL->getKey(),
                    'status' => ProjectStatusEnum::PENDING,
                    'restricted_visibility' => false,
                    'is_instance_shuffled' => true,
                    'scheduled_at' => '2026-05-15',
                    'annotation_task_configuration' => [
                        ['id' => 0, 'answer' => 'No'],
                    ],
                ],
                'subprojects' => [
                    [
                        'name' => 'Batch 1',
                        'status' => ProjectStatusEnum::PENDING,
                        'priority' => SubProjectPriorityEnum::HIGH,
                        'flexible' => true,
                        'auto_submission' => true,
                        'minimum_annotators' => 2,
                        'first_instance_index' => 1,
                        'last_instance_index' => 2,
                        'scheduled_at' => '2026-05-15',
                        'deadline_at' => '2026-06-20',
                        'annotators' => ['annotator.ivy@example.com', 'annotator.jack@example.com'],
                    ],
                    [
                        'name' => 'Batch 2',
                        'status' => ProjectStatusEnum::PENDING,
                        'priority' => SubProjectPriorityEnum::MEDIUM,
                        'flexible' => true,
                        'auto_submission' => true,
                        'minimum_annotators' => 2,
                        'first_instance_index' => 3,
                        'last_instance_index' => 5,
                        'scheduled_at' => '2026-06-01',
                        'deadline_at' => '2026-07-15',
                        'annotators' => ['annotator.karen@example.com', 'annotator.eva@example.com'],
                    ],
                ],
            ],
            [
                'manager_ids' => [$managerCarol->getKey()],
                'annotator_emails' => ['annotator.frank@example.com', 'annotator.grace@example.com'],
                'project' => [
                    'name' => 'Sentiment – Product Reviews',
                    'owner_user_id' => $adminAlice->getKey(),
                    'annotation_task_id' => $sentimentTask->getKey(),
                    'dataset_id' => $productReviews->getKey(),
                    'status' => ProjectStatusEnum::IN_PROGRESS,
                    'restricted_visibility' => true,
                    'is_instance_shuffled' => false,
                    'scheduled_at' => '2026-02-15',
                    'started_at' => '2026-03-10 09:00:00',
                    'annotation_task_configuration' => [
                        ['id' => 0, 'answer' => 'Yes'],
                    ],
                ],
                'subprojects' => [
                    [
                        'name' => 'Full Batch',
                        'status' => ProjectStatusEnum::IN_PROGRESS,
                        'priority' => SubProjectPriorityEnum::LOW,
                        'flexible' => false,
                        'auto_submission' => true,
                        'minimum_annotators' => 1,
                        'first_instance_index' => 1,
                        'last_instance_index' => 5,
                        'scheduled_at' => '2026-02-15',
                        'started_at' => '2026-03-10 09:00:00',
                        'deadline_at' => '2026-05-20',
                        'annotators' => ['annotator.frank@example.com', 'annotator.grace@example.com'],
                    ],
                ],
            ],
            [
                'manager_ids' => [$managerDave->getKey()],
                'annotator_emails' => ['annotator.henry@example.com', 'annotator.ivy@example.com', 'annotator.jack@example.com'],
                'project' => [
                    'name' => 'Toxic Content – Social Media',
                    'owner_user_id' => $adminBob->getKey(),
                    'annotation_task_id' => $toxicTask->getKey(),
                    'dataset_id' => $socialMedia->getKey(),
                    'status' => ProjectStatusEnum::IN_PROGRESS,
                    'restricted_visibility' => false,
                    'is_instance_shuffled' => false,
                    'scheduled_at' => '2026-04-01',
                    'started_at' => '2026-04-15 09:00:00',
                    'deadline_at' => '2026-06-20',
                    'annotation_task_configuration' => [
                        ['id' => 0, 'answer' => 'Yes'],
                        ['id' => 1, 'answer' => 'Yes'],
                    ],
                ],
                'subprojects' => [
                    [
                        'name' => 'Batch 1',
                        'status' => ProjectStatusEnum::IN_PROGRESS,
                        'priority' => SubProjectPriorityEnum::HIGH,
                        'flexible' => true,
                        'auto_submission' => false,
                        'minimum_annotators' => 2,
                        'first_instance_index' => 1,
                        'last_instance_index' => 300,
                        'scheduled_at' => '2026-04-01',
                        'started_at' => '2026-04-15 09:00:00',
                        'deadline_at' => '2026-05-31',
                        'annotators' => ['annotator.henry@example.com', 'annotator.ivy@example.com'],
                    ],
                    [
                        'name' => 'Batch 2',
                        'status' => ProjectStatusEnum::PENDING,
                        'priority' => SubProjectPriorityEnum::HIGH,
                        'flexible' => false,
                        'auto_submission' => false,
                        'minimum_annotators' => 2,
                        'first_instance_index' => 301,
                        'last_instance_index' => 500,
                        'scheduled_at' => '2026-05-10',
                        'deadline_at' => '2026-06-20',
                        'annotators' => ['annotator.jack@example.com', 'annotator.karen@example.com'],
                    ],
                ],
            ],
        ];

        $flaggedCount = 0;
        $confidenceCases = ConfidenceEnum::cases();

        foreach ($projects as $entry) {
            $project = Project::query()->updateOrCreate(
                ['name' => $entry['project']['name']],
                $entry['project'],
            );
            $expectsConfidence = $project->expectsConfidence();

            ProjectManager::query()->firstOrCreate(
                ['project_id' => $project->id, 'user_id' => $project->owner_user_id],
                ['accepted' => true],
            );

            foreach ($entry['manager_ids'] as $managerId) {
                ProjectManager::query()->firstOrCreate(
                    ['project_id' => $project->id, 'user_id' => $managerId],
                    ['accepted' => true],
                );
            }

            foreach ($entry['annotator_emails'] as $email) {
                $annotator = User::query()->where('email', $email)->firstOrFail();
                AnnotatorOfManager::query()->firstOrCreate([
                    'manager_id' => $project->owner_user_id,
                    'annotator_id' => $annotator->id,
                ]);
            }

            if ($entry['project']['is_instance_shuffled']) {
                /** @var int $datasetId */
                $datasetId = $entry['project']['dataset_id'];
                $shuffled = resolve(DatasetService::class)->generateShuffledIndexArray($datasetId);
                $counter = count($shuffled);
                for ($i = 0; $i < $counter; $i++) {
                    InstanceShuffleMapper::query()->updateOrCreate(
                        ['new_index' => $i + 1, 'project_id' => $project->getKey()],
                        ['old_index' => $shuffled[$i]],
                    );
                }
            }

            /** @var array<int, int|string> $projectAnnotatorIds */
            $projectAnnotatorIds = [];

            foreach ($entry['subprojects'] as $spData) {
                $annotatorEmails = $spData['annotators'];
                $shuffle = $spData['shuffle'] ?? false;
                $forcedSubmittedCount = $spData['submitted_count'] ?? null;
                $forcedPendingCount = $spData['pending_count'] ?? null;
                unset($spData['annotators'], $spData['shuffle'], $spData['submitted_count'], $spData['pending_count']);

                $subProject = SubProject::query()->updateOrCreate(
                    ['project_id' => $project->getKey(), 'name' => $spData['name']],
                    array_merge($spData, ['project_id' => $project->getKey()]),
                );

                $datasetInstances = DatasetInstance::query()
                    ->where('dataset_id', $entry['project']['dataset_id'])
                    ->whereBetween('index', [$spData['first_instance_index'], $spData['last_instance_index']])
                    ->orderBy('index')
                    ->select('id')
                    ->get();

                // Build a project-position → dataset_instance_id lookup (1-based)
                $instanceByProjectPos = [];
                $pos = 1;
                foreach ($datasetInstances as $instance) {
                    $instanceByProjectPos[$pos++] = $instance->getKey();
                }

                $instanceCount = count($instanceByProjectPos);
                $projectPositions = range(1, $instanceCount);

                $now = now();

                $canBePending = ! $subProject->auto_submission;

                foreach ($annotatorEmails as $email) {
                    $annotator = User::query()->where('email', $email)->firstOrFail();
                    $assignment = AnnotationAssignment::query()->firstOrCreate(
                        ['user_id' => $annotator->getKey(), 'sub_project_id' => $subProject->getKey()],
                        ['is_instance_shuffled' => $shuffle],
                    );
                    /** @var int|string $annotatorKey */
                    $annotatorKey = $annotator->getKey();
                    $projectAnnotatorIds[] = $annotatorKey;

                    // Each annotator gets their own permutation when shuffle is enabled.
                    // annotator_instance_index is sequential; the project side varies per annotator.
                    $orderedProjectPositions = $shuffle
                        ? Arr::shuffle($projectPositions)
                        : $projectPositions;

                    $rows = [];
                    $isFirst = true;

                    if ($forcedSubmittedCount !== null && $forcedPendingCount !== null) {
                        Annotation::query()->where('annotation_assignment_id', $assignment->getKey())->delete();
                        $totalDone = $forcedSubmittedCount + $forcedPendingCount;

                        foreach ($orderedProjectPositions as $annotatorPos => $projectPos) {
                            $annotatorIndex = $annotatorPos + 1;
                            $isSubmitted = $annotatorIndex <= $forcedSubmittedCount;
                            $isPending = ! $isSubmitted && $annotatorIndex <= $totalDone;
                            $isDone = $isSubmitted || $isPending;

                            $rows[] = [
                                'annotation_assignment_id' => $assignment->getKey(),
                                'dataset_instance_id' => $instanceByProjectPos[$projectPos],
                                'project_instance_index' => $projectPos,
                                'annotator_instance_index' => $annotatorIndex,
                                'annotations' => $isDone ? '{}' : null,
                                'pending' => $isPending,
                                'confidence' => $expectsConfidence && $isSubmitted
                                    ? $confidenceCases[random_int(0, count($confidenceCases) - 1)]->value
                                    : null,
                                'last_edited_by' => $annotator->getKey(),
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    } else {
                        $cutoff = $subProject->status === ProjectStatusEnum::IN_PROGRESS
                            ? random_int(0, $instanceCount)
                            : 0;

                        foreach ($orderedProjectPositions as $annotatorPos => $projectPos) {
                            $annotatorIndex = $annotatorPos + 1;
                            $isDone = $annotatorIndex <= $cutoff;
                            $isFlagged = ! $isDone && $flaggedCount < 2 && $isFirst;
                            $isFirst = false;

                            $flagNotificationThreadId = null;
                            if ($isFlagged) {
                                $flaggedCount++;
                                $flagNotificationThreadId = NotificationThread::query()->create([
                                    'type' => NotificationThreadTypeEnum::FLAG_NOTIFICATION,
                                ])->getKey();
                            }

                            $rows[] = [
                                'annotation_assignment_id' => $assignment->getKey(),
                                'dataset_instance_id' => $instanceByProjectPos[$projectPos],
                                'project_instance_index' => $projectPos,
                                'annotator_instance_index' => $annotatorIndex,
                                'annotations' => $isDone ? '{}' : null,
                                'pending' => false,
                                'flag_notification_thread_id' => $flagNotificationThreadId,
                                'confidence' => $expectsConfidence && $isDone
                                    ? $confidenceCases[random_int(0, count($confidenceCases) - 1)]->value
                                    : null,
                                'last_edited_by' => $annotator->getKey(),
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }

                        // Force the last annotated row for this annotator to pending.
                        if ($canBePending) {
                            for ($i = count($rows) - 1; $i >= 0; $i--) {
                                if ($rows[$i]['annotations'] !== null) {
                                    $rows[$i]['pending'] = true;

                                    break;
                                }
                            }
                        }
                    }

                    Annotation::query()->insertOrIgnore($rows);
                }
            }

            $project->annotators()->syncWithoutDetaching(array_values(array_unique($projectAnnotatorIds)));
        }
    }
}
