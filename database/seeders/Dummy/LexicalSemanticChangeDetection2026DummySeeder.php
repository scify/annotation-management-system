<?php

declare(strict_types=1);

namespace Database\Seeders\Dummy;

use App\Enums\ProjectStatusEnum;
use App\Enums\SubProjectPriorityEnum;
use App\Models\AnnotationTask;
use App\Models\AnnotatorOfManager;
use App\Models\Dataset;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\SubProject;
use App\Models\User;
use App\Services\SubProject\SubProjectWriteService;
use Database\Seeders\AnnotationTasks\LexicalSemanticChangeDetection2026Seeder;
use Illuminate\Database\Seeder;

class LexicalSemanticChangeDetection2026DummySeeder extends Seeder {
    public function run(): void {
        $this->call(LexicalSemanticChangeDetection2026Seeder::class);

        $carol = User::query()->where('email', 'manager.carol@example.com')->firstOrFail();
        $eva = User::query()->where('email', 'annotator.eva@example.com')->firstOrFail();
        $frank = User::query()->where('email', 'annotator.frank@example.com')->firstOrFail();

        $task = AnnotationTask::query()->where('title', 'Lexical Semantic Change Detection')->firstOrFail();
        $dataset = Dataset::query()->where('name', 'Lexical Semantic Change Detection 2026')->firstOrFail();

        // Give manager_carol access to the task and its dataset
        $task->connectedUsers()->syncWithoutDetaching([$carol->id]);

        // Ensure carol has eva and frank in her annotator pool
        AnnotatorOfManager::query()->firstOrCreate(['manager_id' => $carol->id, 'annotator_id' => $eva->id]);
        AnnotatorOfManager::query()->firstOrCreate(['manager_id' => $carol->id, 'annotator_id' => $frank->id]);

        // Create the project under manager_carol
        $project = Project::query()->updateOrCreate(
            ['name' => 'Lexical Semantic Change Detection 2026'],
            [
                'owner_user_id' => $carol->id,
                'annotation_task_id' => $task->id,
                'dataset_id' => $dataset->id,
                'status' => ProjectStatusEnum::PENDING,
                'restricted_visibility' => false,
                'is_instance_shuffled' => false,
                'annotation_task_configuration' => [
                    ['id' => 0, 'answer' => 'Yes'],
                    ['id' => 1, 'answer' => 'Yes'],
                ],
            ],
        );

        ProjectManager::query()->firstOrCreate(
            ['project_id' => $project->id, 'user_id' => $carol->id],
            ['accepted' => true],
        );

        $project->annotators()->syncWithoutDetaching([$eva->id, $frank->id]);

        $subProjectService = resolve(SubProjectWriteService::class);
        $annotatorIds = [$eva->id, $frank->id];

        $batches = [
            ['name' => 'No Shuffle – Strict',          'shuffle' => false, 'is_flexible' => false, 'requires_confirmation' => false],
            ['name' => 'No Shuffle – Flexible Auto',   'shuffle' => false, 'is_flexible' => true,  'requires_confirmation' => false],
            ['name' => 'No Shuffle – Flexible Manual', 'shuffle' => false, 'is_flexible' => true,  'requires_confirmation' => true],
            ['name' => 'Shuffle – Strict',             'shuffle' => true,  'is_flexible' => false, 'requires_confirmation' => false],
            ['name' => 'Shuffle – Flexible Auto',      'shuffle' => true,  'is_flexible' => true,  'requires_confirmation' => false],
            ['name' => 'Shuffle – Flexible Manual',    'shuffle' => true,  'is_flexible' => true,  'requires_confirmation' => true],
        ];

        foreach ($batches as $batch) {
            $subProjectService->storeSubProject($project->id, [
                'annotator_ids' => $annotatorIds,
                'name' => $batch['name'],
                'priority' => SubProjectPriorityEnum::MEDIUM,
                'is_flexible' => $batch['is_flexible'],
                'requires_confirmation' => $batch['requires_confirmation'],
                'minimum_annotations' => count($annotatorIds),
                'from_instance' => 1,
                'to_instance' => 25,
                'scheduled_at' => null,
                'deadline_at' => null,
                'shuffle' => $batch['shuffle'],
            ]);
        }

        // Start both subprojects (also promotes the project to IN_PROGRESS)
        $subProjects = SubProject::query()
            ->with('project')
            ->where('project_id', $project->id)
            ->get();

        foreach ($subProjects as $subProject) {
            $subProjectService->changeStatus($subProject, ProjectStatusEnum::IN_PROGRESS);
        }
    }
}
