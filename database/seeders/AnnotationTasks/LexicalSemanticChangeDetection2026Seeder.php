<?php

declare(strict_types=1);

namespace Database\Seeders\AnnotationTasks;

use App\Enums\AnnotationTaskTypeEnum;
use App\Models\AnnotationTask;
use App\Models\Dataset;
use App\Models\DatasetInstance;
use App\Models\TaskTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LexicalSemanticChangeDetection2026Seeder extends Seeder {
    public function run(): void {
        $jsonPath = base_path('datasets/Lexical_Semantic_Change_Detection/2026_Lexical Semantic Change Detection_dataset.json');

        /** @var array<int, array{word: string, senses: list<string>, corpus1_sentences: list<string>, corpus2_sentences: list<string>}> $data */
        $data = json_decode((string) file_get_contents($jsonPath), associative: true);

        $dataset = Dataset::query()->updateOrCreate(
            ['name' => 'Lexical Semantic Change Detection 2026'],
            [
                'description' => 'Word-usage pairs drawn from two historical corpora for lexical semantic change detection. Each entry contains a target word, its sense definitions, and sample sentences from each corpus.',
                'is_available' => true,
                'size' => 0,
            ],
        );

        /** @var int|string $datasetKey */
        $datasetKey = $dataset->getKey();
        $datasetId = (int) $datasetKey;

        $index = 1;

        foreach ($data as $entry) {
            $pairCount = min(count($entry['corpus1_sentences']), count($entry['corpus2_sentences']));

            for ($j = 0; $j < $pairCount; $j++) {
                DatasetInstance::query()->updateOrCreate(
                    ['index' => $index, 'dataset_id' => $datasetId],
                    [
                        'content' => [
                            'word' => $entry['word'],
                            'senses' => $entry['senses'],
                            'first_corpus_sentence' => $entry['corpus1_sentences'][$j],
                            'second_corpus_sentence' => $entry['corpus2_sentences'][$j],
                        ],
                    ],
                );
                $index++;
            }
        }

        $dataset->update(['size' => $index - 1]);

        $tagNames = [
            'text',
            'semantic change',
            'word sense disambiguation',
        ];

        foreach ($tagNames as $tagName) {
            TaskTag::query()->firstOrCreate(['name' => $tagName]);
        }

        $annotationTask = AnnotationTask::query()->updateOrCreate(
            ['title' => 'Lexical Semantic Change Detection'],
            [
                'short_description' => 'Determine if a target word carries the same meaning in two texts.',
                'description' => 'In this task, you will determine whether a target word is used with the same meaning in two different texts. You will be shown a target word, a description of one of its meanings, and two texts containing the target word. Read both texts carefully and decide whether the target word has the same meaning in both cases, taking the provided description into account. If the available context is insufficient or you cannot confidently determine the meaning, select Cannot Decide.',
                'weight' => 4,
                'task_type' => AnnotationTaskTypeEnum::LEXICAL_SEMANTIC_CHANGE_DETECTION,
                'customization_options' => [
                    [
                        'id' => 0,
                        'question' => 'Do you want to allow the annotators to mark their confidence?',
                        'answers' => ['Yes', 'No'],
                        'parameters' => ['low', 'medium', 'high'],
                    ],
                    [
                        'id' => 1,
                        'question' => 'Do you want to allow "Cannot Decide" as an answer?',
                        'answers' => ['Yes', 'No'],
                        'parameters' => ['Cannot Decide'],
                    ],
                ],
            ],
        );

        $allTags = TaskTag::query()->pluck('id', 'name');

        $tagIds = collect($tagNames)
            ->map(fn (string $name): mixed => $allTags->get($name))
            ->filter()
            ->values()
            ->all();

        $annotationTask->tags()->sync($tagIds);

        /** @var int|string $taskKey */
        $taskKey = $annotationTask->getKey();
        $taskId = (int) $taskKey;

        $alreadyLinked = DB::table('dataset_annotation_tasks')
            ->where('dataset_id', $datasetId)
            ->where('annotation_task_id', $taskId)
            ->exists();

        if (! $alreadyLinked) {
            DB::table('dataset_annotation_tasks')->insert([
                'dataset_id' => $datasetId,
                'annotation_task_id' => $taskId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
