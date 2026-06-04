<?php

declare(strict_types=1);

namespace App\Queries;

use Illuminate\Support\Facades\DB;

final readonly class GetAnnotationTaskIdsByManagerQuery {
    /**
     * @return array<int, int>
     */
    public function get(int $managerId): array {
        /** @var array<int, int> */
        return DB::table('annotation_task_user')
            ->where('user_id', $managerId)
            ->pluck('annotation_task_id')
            ->all();
    }
}
