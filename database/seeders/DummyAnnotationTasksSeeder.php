<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AnnotationTask;
use Illuminate\Database\Seeder;

class DummyAnnotationTasksSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $tasks = [
            [
                'title' => 'Sentiment Analysis',
                'description' => 'Label each text snippet as positive, negative, or neutral based on the expressed sentiment.',
                'guidelines_url' => 'https://example.com/guidelines/sentiment-analysis',
                'weight' => 3,
            ],
            [
                'title' => 'Named Entity Recognition',
                'description' => 'Identify and tag named entities such as persons, organisations, and locations within the provided texts.',
                'guidelines_url' => 'https://example.com/guidelines/ner',
                'weight' => 5,
            ],
            [
                'title' => 'Image Classification',
                'description' => 'Assign one or more category labels to each image according to the defined taxonomy.',
                'guidelines_url' => null,
                'weight' => 4,
            ],
            [
                'title' => 'Toxic Content Detection',
                'description' => 'Flag content that contains hate speech, harassment, or other policy-violating material.',
                'guidelines_url' => 'https://example.com/guidelines/toxic-content',
                'weight' => 5,
            ],
            [
                'title' => 'Summarisation Quality Rating',
                'description' => 'Rate the quality of machine-generated summaries on fluency, coherence, and factual accuracy.',
                'guidelines_url' => null,
                'weight' => 2,
            ],
        ];

        foreach ($tasks as $task) {
            AnnotationTask::query()->updateOrCreate(
                ['title' => $task['title']],
                $task,
            );
        }
    }
}
