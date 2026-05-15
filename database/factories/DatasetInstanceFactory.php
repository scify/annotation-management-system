<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Dataset;
use App\Models\DatasetInstance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DatasetInstance>
 */
class DatasetInstanceFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'index' => fake()->unique()->numberBetween(0, 999999),
            'dataset_id' => Dataset::factory(),
            'content' => [],
        ];
    }
}
