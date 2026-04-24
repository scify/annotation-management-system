<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AnnotationTask;
use App\Models\Dataset;
use App\Models\DatasetInstance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDatasetsSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $datasets = [
            [
                'name' => 'News Articles EN',
                'description' => 'English-language news articles collected from major outlets, suitable for sentiment and NER tasks.',
                'is_available' => true,
            ],
            [
                'name' => 'News Articles EL',
                'description' => 'Greek-language news articles collected from major outlets, suitable for sentiment and NER tasks.',
                'is_available' => true,
            ],
            [
                'name' => 'Social Media Posts',
                'description' => 'Short-form user posts from social platforms, used for toxic content and sentiment annotation.',
                'is_available' => true,
            ],
            [
                'name' => 'Medical Records Corpus',
                'description' => 'Anonymised clinical notes for named entity and classification annotation tasks.',
                'is_available' => true,
            ],
            [
                'name' => 'Product Reviews',
                'description' => 'E-commerce product reviews across multiple categories for quality rating and sentiment tasks.',
                'is_available' => true,
            ],
            [
                'name' => 'Image Batch 2024-Q4',
                'description' => 'Curated image collection from Q4 2024, intended for image classification annotation.',
                'is_available' => true,
            ],
        ];

        foreach ($datasets as $dataset) {
            $added_dataset_id = Dataset::query()->updateOrCreate(
                ['name' => $dataset['name']],
                $dataset,
            )->getKey();
            // add dummy instances
            for ($i = 0; $i < 5; $i++) {
                DatasetInstance::query()->updateOrCreate(
                    ['index' => $i, 'dataset_id' => $added_dataset_id],
                    [
                        'index' => $i,
                        'dataset_id' => $added_dataset_id,
                        'content' => [
                            'text1' => 'First additional text for instance ' . $i,
                            'text2' => 'Second additional text for instance ' . $i,
                            'question' => [
                                'prompt' => sprintf('Sample question for instance %d?', $i),
                                'answers' => ['Answer A', 'Answer B', 'Answer C'],
                            ],
                        ],
                    ],
                );
            }

        }

        $datasetsByName = Dataset::query()
            ->whereIn('name', ['News Articles EN', 'News Articles EL', 'Social Media Posts', 'Medical Records Corpus', 'Product Reviews', 'Image Batch 2024-Q4'])
            ->get()
            ->keyBy('name');

        $tasksByTitle = AnnotationTask::query()
            ->whereIn('title', ['Named Entity Recognition', 'Toxic Content Detection', 'Summarisation Quality Rating', 'Sentiment Analysis', 'Image Classification'])
            ->get()
            ->keyBy('title');

        $links = [
            ['News Articles EN',    'Named Entity Recognition'],
            ['News Articles EL',    'Named Entity Recognition'],
            ['Social Media Posts',  'Toxic Content Detection'],
            ['Medical Records Corpus', 'Summarisation Quality Rating'],
            ['Product Reviews',     'Sentiment Analysis'],
            ['Image Batch 2024-Q4', 'Image Classification'],
        ];

        foreach ($links as [$datasetName, $taskTitle]) {
            $dataset = $datasetsByName->get($datasetName);
            $task = $tasksByTitle->get($taskTitle);

            if ($dataset !== null && $task !== null) {
                $datasetId = (int) $dataset->getKey();
                $taskId = (int) $task->getKey();

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
    }
}
