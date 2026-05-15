<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProjectStatusEnum;
use App\Models\AnnotationTask;
use App\Models\Dataset;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'name' => fake()->words(4, true),
            'owner_user_id' => User::factory(),
            'annotation_task_id' => AnnotationTask::factory(),
            'dataset_id' => Dataset::factory(),
            'status' => ProjectStatusEnum::IN_PROGRESS,
            'restricted_visibility' => false,
            'is_instance_shuffled' => false,
            'annotation_task_configuration' => null,
        ];
    }
}
