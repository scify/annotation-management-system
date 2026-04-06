<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DatasetInstance;
use App\Models\DatasetInstanceElement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DatasetInstanceElement>
 */
class DatasetInstanceElementFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        $index = $this->faker->numberBetween(1, 10);

        return [
            'index' => $index,
            'key' => 'key' . $index,
            'value' => 'value' . $index,
            'dataset_instance_id' => DatasetInstance::factory(),
        ];
    }
}
