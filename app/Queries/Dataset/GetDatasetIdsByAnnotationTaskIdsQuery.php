<?php

declare(strict_types=1);

namespace App\Queries\Dataset;

use Illuminate\Support\Facades\DB;

final readonly class GetDatasetIdsByAnnotationTaskIdsQuery {
    /**
     * @param  array<int, int>  $annotationTaskIds
     *
     * @return array<int, int>
     */
    public function get(array $annotationTaskIds): array {
        if ($annotationTaskIds === []) {
            return [];
        }

        /** @var array<int, int> */
        return DB::table('dataset_annotation_tasks')
            ->whereIn('annotation_task_id', $annotationTaskIds)
            ->pluck('dataset_id')
            ->all();
    }
}
