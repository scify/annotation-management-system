<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AnnotationAssignment;
use App\Models\AnnotationSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnnotationSession>
 */
class AnnotationSessionFactory extends Factory {
    public function definition(): array {
        return [
            'annotation_assignment_id' => AnnotationAssignment::factory(),
            'started_timestamp' => now(),
            'ended_timestamp' => null,
            'session_annotations_count' => 0,
            'next_annotation_id' => null,
        ];
    }
}
