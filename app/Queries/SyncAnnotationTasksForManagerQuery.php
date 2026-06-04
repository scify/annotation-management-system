<?php

declare(strict_types=1);

namespace App\Queries;

use Illuminate\Support\Facades\DB;

final readonly class SyncAnnotationTasksForManagerQuery {
    /**
     * @param  array<int, int>  $annotationTaskIds
     */
    public function sync(int $managerId, array $annotationTaskIds): void {
        DB::table('annotation_task_user')->where('user_id', $managerId)->delete();

        foreach ($annotationTaskIds as $taskId) {
            DB::table('annotation_task_user')->insert([
                'annotation_task_id' => $taskId,
                'user_id' => $managerId,
            ]);
        }
    }
}
