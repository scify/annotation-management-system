<?php

declare(strict_types=1);

namespace App\Queries;

use Illuminate\Support\Facades\DB;

final readonly class ConnectManagerToDatasetsQuery {
    /**
     * @param  array<int, int>  $datasetIds
     */
    public function connect(int $managerId, array $datasetIds): void {
        foreach ($datasetIds as $datasetId) {
            DB::table('dataset_user')->insert([
                'dataset_id' => $datasetId,
                'user_id' => $managerId,
            ]);
        }
    }
}
