<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\AnnotatorOfProject;
use Illuminate\Support\Facades\Date;

final readonly class AttachAnnotatorsToProjectQuery {
    /**
     * @param  array<int, int>  $annotatorIds
     */
    public function attach(int $projectId, array $annotatorIds): void {
        $now = Date::now();
        $rows = array_map(fn (int $userId): array => [
            'project_id' => $projectId,
            'user_id' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $annotatorIds);

        AnnotatorOfProject::query()->insertOrIgnore($rows);
    }
}
