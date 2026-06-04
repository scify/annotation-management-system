<?php

declare(strict_types=1);

namespace App\Queries;

use Illuminate\Support\Facades\DB;

final readonly class ConnectManagerToAnnotationTasksQuery {
    /**
     * @param  array<int, int>  $annotationTaskIds
     */
    public function connect(int $managerId, array $annotationTaskIds): void {
        foreach ($annotationTaskIds as $taskId) {
            DB::table('annotation_task_user')->insert([
                'annotation_task_id' => $taskId,
                'user_id' => $managerId,
            ]);
        }
    }
}
