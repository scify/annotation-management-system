<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ConfidenceEnum;
use App\Models\Annotation;
use App\Models\Confidence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Confidence>
 */
class ConfidenceFactory extends Factory {
    /**
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'annotation_id' => Annotation::factory(),
            'value' => $this->faker->randomElement(ConfidenceEnum::cases()),
        ];
    }
}
