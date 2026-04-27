<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ProjectStatusEnum;
use App\Enums\SubProjectPriorityEnum;
use App\Models\AnnotationAssignment;
use App\Models\AnnotationTask;
use App\Models\Dataset;
use App\Models\InstanceShuffleMapper;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use App\Services\Dataset\DatasetService;
use Illuminate\Database\Seeder;

class DummyProjectSeeder extends Seeder {
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
                'project' => [
                    'name' => 'NER – English News',
                    'description' => 'Named entity recognition on English-language news articles.',
                    'owner_user_id' => $managerCarol->getKey(),
                    'annotation_task_id' => $nerTask->getKey(),
                    'dataset_id' => $newsEN->getKey(),
                    'status' => ProjectStatusEnum::IN_PROGRESS,
                    'restricted_visibility' => false,
                    'is_instance_shuffled' => false,
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
                        'first_instance_index' => 0,
                        'last_instance_index' => 2,
                        'scheduled_at' => '2026-03-01',
                        'started_at' => '2026-04-01 09:00:00',
                        'deadline_at' => '2026-05-31',
                        'annotators' => ['annotator.eva@example.com', 'annotator.grace@example.com'],
                    ],
                    [
                        'name' => 'Batch 2',
                        'status' => ProjectStatusEnum::PENDING,
                        'priority' => SubProjectPriorityEnum::MEDIUM,
                        'flexible' => false,
                        'auto_submission' => true,
                        'minimum_annotators' => 1,
                        'first_instance_index' => 3,
                        'last_instance_index' => 4,
                        'scheduled_at' => '2026-05-01',
                        'deadline_at' => '2026-06-30',
                        'annotators' => ['annotator.frank@example.com', 'annotator.henry@example.com'],
                    ],
                ],
            ],
            [
                'project' => [
                    'name' => 'NER – Greek News',
                    'description' => 'Named entity recognition on Greek-language news articles.',
                    'owner_user_id' => $managerDave->getKey(),
                    'annotation_task_id' => $nerTask->getKey(),
                    'dataset_id' => $newsEL->getKey(),
                    'status' => ProjectStatusEnum::PENDING,
                    'restricted_visibility' => false,
                    'is_instance_shuffled' => true,
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
                        'first_instance_index' => 0,
                        'last_instance_index' => 1,
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
                        'first_instance_index' => 2,
                        'last_instance_index' => 4,
                        'scheduled_at' => '2026-06-01',
                        'deadline_at' => '2026-07-15',
                        'annotators' => ['annotator.karen@example.com', 'annotator.eva@example.com'],
                    ],
                ],
            ],
            [
                'project' => [
                    'name' => 'Sentiment – Product Reviews',
                    'description' => 'Sentiment analysis on e-commerce product reviews.',
                    'owner_user_id' => $adminAlice->getKey(),
                    'annotation_task_id' => $sentimentTask->getKey(),
                    'dataset_id' => $productReviews->getKey(),
                    'status' => ProjectStatusEnum::IN_PROGRESS,
                    'restricted_visibility' => false,
                    'is_instance_shuffled' => false,
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
                        'first_instance_index' => 0,
                        'last_instance_index' => 4,
                        'scheduled_at' => '2026-02-15',
                        'started_at' => '2026-03-10 09:00:00',
                        'deadline_at' => '2026-05-20',
                        'annotators' => ['annotator.frank@example.com', 'annotator.grace@example.com'],
                    ],
                ],
            ],
            [
                'project' => [
                    'name' => 'Toxic Content – Social Media',
                    'description' => 'Toxic content detection on social media posts.',
                    'owner_user_id' => $adminBob->getKey(),
                    'annotation_task_id' => $toxicTask->getKey(),
                    'dataset_id' => $socialMedia->getKey(),
                    'status' => ProjectStatusEnum::IN_PROGRESS,
                    'restricted_visibility' => false,
                    'is_instance_shuffled' => false,
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
                        'flexible' => false,
                        'auto_submission' => false,
                        'minimum_annotators' => 2,
                        'first_instance_index' => 0,
                        'last_instance_index' => 2,
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
                        'first_instance_index' => 3,
                        'last_instance_index' => 4,
                        'scheduled_at' => '2026-05-10',
                        'deadline_at' => '2026-06-20',
                        'annotators' => ['annotator.jack@example.com', 'annotator.karen@example.com'],
                    ],
                ],
            ],
        ];

        foreach ($projects as $entry) {
            $project = Project::query()->updateOrCreate(
                ['name' => $entry['project']['name']],
                $entry['project'],
            );

            if ($entry['project']['is_instance_shuffled']) {
                $datasetId = $entry['project']['dataset_id'];
                $shuffled = new DatasetService()->generateShuffledIndexArray($datasetId);
                $counter = count($shuffled);
                for ($i = 0; $i < $counter; $i++) {
                    InstanceShuffleMapper::query()->updateOrCreate(
                        ['new_index' => $i, 'project_id' => $project->getKey()],
                        ['old_index' => $shuffled[$i]],
                    );
                }
            }

            foreach ($entry['subprojects'] as $spData) {
                $annotatorEmails = $spData['annotators'];
                unset($spData['annotators']);

                $subProject = SubProject::query()->updateOrCreate(
                    ['project_id' => $project->getKey(), 'name' => $spData['name']],
                    array_merge($spData, ['project_id' => $project->getKey()]),
                );

                foreach ($annotatorEmails as $email) {
                    $annotator = User::query()->where('email', $email)->firstOrFail();
                    AnnotationAssignment::query()->firstOrCreate(
                        ['user_id' => $annotator->getKey(), 'sub_project_id' => $subProject->getKey()],
                    );
                }
            }
        }
    }
}
