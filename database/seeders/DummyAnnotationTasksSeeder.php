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
        $confidenceQuestion = [
            'id' => 0,
            'question' => 'Do you want to allow the annotators to mark their confidence?',
            'answers' => ['Yes', 'No'],
            'parameters' => ['low', 'medium', 'high'],
        ];

        $notSureQuestion = [
            'id' => 1,
            'question' => 'Do you want to allow the answer "I am not sure"?',
            'answers' => ['Yes', 'No'],
            'parameters' => ['Not sure'],
        ];

        $tasks = [
            [
                'title' => 'Sentiment Analysis',
                'description' => 'Label each text snippet as positive, negative, or neutral based on the expressed sentiment.',
                'guidelines_url' => 'https://example.com/guidelines/sentiment-analysis',
                'weight' => 3,
                'customization_options' => [$confidenceQuestion],
            ],
            [
                'title' => 'Named Entity Recognition',
                'description' => 'Identify and tag named entities such as persons, organisations, and locations within the provided texts.',
                'guidelines_url' => 'https://example.com/guidelines/ner',
                'weight' => 5,
                'customization_options' => [$confidenceQuestion],
            ],
            [
                'title' => 'Image Classification',
                'description' => 'Assign one or more category labels to each image according to the defined taxonomy.',
                'guidelines_url' => null,
                'weight' => 4,
                'customization_options' => [$confidenceQuestion, $notSureQuestion],
            ],
            [
                'title' => 'Toxic Content Detection',
                'description' => 'Flag content that contains hate speech, harassment, or other policy-violating material.',
                'guidelines_url' => 'https://example.com/guidelines/toxic-content',
                'weight' => 5,
                'customization_options' => [$confidenceQuestion, $notSureQuestion],
            ],
            [
                'title' => 'Summarisation Quality Rating',
                'description' => 'Rate the quality of machine-generated summaries on fluency, coherence, and factual accuracy.',
                'guidelines_url' => null,
                'weight' => 2,
                'customization_options' => [$confidenceQuestion],
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
