<?php

declare(strict_types=1);

namespace App\Queries\SubProject;

use App\Models\AnnotationAssignment;

final readonly class GetAnnotatorIdsBySubProjectQuery {
    /**
     * @return array<int, int>
     */
    public function get(int $subProjectId): array {
        /** @var array<int, int> $result */
        $result = AnnotationAssignment::query()
            ->where('sub_project_id', $subProjectId)
            ->pluck('user_id')
            ->all();

        return $result;
    }

    public function isInstanceShuffled(int $subProjectId): bool {
        return (bool) AnnotationAssignment::query()
            ->where('sub_project_id', $subProjectId)
            ->value('is_instance_shuffled');
    }
}
