<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AnnotationTask;
use App\Models\TaskTag;
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
                'short_description' => 'Classify text snippets as positive, negative, or neutral.',
                'description' => 'Label each text snippet as positive, negative, or neutral based on the expressed sentiment.',
                'guidelines_url' => 'https://example.com/guidelines/sentiment-analysis',
                'weight' => 3,
                'customization_options' => [$confidenceQuestion],
                'tags' => ['text', 'sentiment analysis'],
            ],
            [
                'title' => 'Named Entity Recognition',
                'short_description' => 'Tag persons, organisations, and locations in text.',
                'description' => 'Identify and tag named entities such as persons, organisations, and locations within the provided texts.',
                'guidelines_url' => 'https://example.com/guidelines/ner',
                'weight' => 5,
                'customization_options' => [$confidenceQuestion],
                'tags' => ['text'],
            ],
            [
                'title' => 'Image Classification',
                'short_description' => 'Assign category labels to images from a defined taxonomy.',
                'description' => 'Assign one or more category labels to each image according to the defined taxonomy.',
                'guidelines_url' => null,
                'weight' => 4,
                'customization_options' => [$confidenceQuestion, $notSureQuestion],
                'tags' => ['image', 'classification'],
            ],
            [
                'title' => 'Toxic Content Detection',
                'short_description' => 'Flag hate speech and policy-violating content.',
                'description' => 'Flag content that contains hate speech, harassment, or other policy-violating material.',
                'guidelines_url' => 'https://example.com/guidelines/toxic-content',
                'weight' => 5,
                'customization_options' => [$confidenceQuestion, $notSureQuestion],
                'tags' => ['text', 'classification'],
            ],
            [
                'title' => 'Summarisation Quality Rating',
                'short_description' => 'Rate machine-generated summaries on fluency and accuracy.',
                'description' => 'Rate the quality of machine-generated summaries on fluency, coherence, and factual accuracy.',
                'guidelines_url' => null,
                'weight' => 2,
                'customization_options' => [$confidenceQuestion],
                'tags' => ['text', 'summarisation'],
            ],
        ];

        $allTags = TaskTag::query()->pluck('id', 'name');

        foreach ($tasks as $task) {
            $tagNames = $task['tags'];
            unset($task['tags']);

            $annotationTask = AnnotationTask::query()->updateOrCreate(
                ['title' => $task['title']],
                $task,
            );

            $tagIds = collect($tagNames)
                ->map(fn (string $name): mixed => $allTags->get($name))
                ->filter()
                ->values()
                ->all();

            $annotationTask->tags()->sync($tagIds);
        }
    }
}
