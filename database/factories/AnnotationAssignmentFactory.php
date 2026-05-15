<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AnnotationAssignment;
use App\Models\SubProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnnotationAssignment>
 */
class AnnotationAssignmentFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'user_id' => User::factory(),
            'sub_project_id' => SubProject::factory(),
        ];
    }
}
