<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\DatasetInstance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Annotation>
 */
class AnnotationFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'annotation_assignment_id' => AnnotationAssignment::factory(),
            'dataset_instance_id' => DatasetInstance::factory(),
            'index' => 1,
            'annotations' => [],
        ];
    }
}
