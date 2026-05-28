<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProjectStatusEnum;
use App\Enums\SubProjectPriorityEnum;
use App\Models\Project;
use App\Models\SubProject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubProject>
 */
class SubProjectFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->words(2, true),
            'status' => ProjectStatusEnum::IN_PROGRESS,
            'priority' => SubProjectPriorityEnum::MEDIUM,
            'flexible' => false,
            'auto_submission' => true,
            'minimum_annotators' => 1,
            'first_instance_index' => 1,
            'last_instance_index' => fake()->numberBetween(10, 100),
        ];
    }
}
