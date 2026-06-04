<?php

declare(strict_types=1);

namespace App\Queries;

use Illuminate\Support\Facades\DB;

final readonly class GetDatasetIdsByManagerQuery {
    /**
     * @return array<int, int>
     */
    public function get(int $managerId): array {
        /** @var array<int, int> */
        return DB::table('dataset_user')
            ->where('user_id', $managerId)
            ->pluck('dataset_id')
            ->all();
    }
}
