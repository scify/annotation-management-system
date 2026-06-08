<?php

declare(strict_types=1);

namespace App\Queries\Manager;

use Illuminate\Support\Facades\DB;

final readonly class SyncDatasetsForManagerQuery {
    /**
     * @param  array<int, int>  $datasetIds
     */
    public function sync(int $managerId, array $datasetIds): void {
        DB::table('dataset_user')->where('user_id', $managerId)->delete();

        foreach ($datasetIds as $datasetId) {
            DB::table('dataset_user')->insert([
                'dataset_id' => $datasetId,
                'user_id' => $managerId,
            ]);
        }
    }
}
