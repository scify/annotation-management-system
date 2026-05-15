<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AnnotationTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnnotationTask>
 */
class AnnotationTaskFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'title' => fake()->words(3, true),
            'short_description' => fake()->sentence(),
            'weight' => fake()->numberBetween(1, 5),
        ];
    }
}
