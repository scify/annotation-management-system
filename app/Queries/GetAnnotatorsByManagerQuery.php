<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\AnnotatorOfManager;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetAnnotatorsByManagerQuery {
    /**
     * @return Collection<int, User>
     */
    public function get(int $managerId): Collection {
        $annotatorIds = AnnotatorOfManager::query()
            ->where('manager_id', $managerId)
            ->pluck('annotator_id')
            ->all();

        if ($annotatorIds === []) {
            return new Collection();
        }

        return User::query()->whereIn('id', $annotatorIds)->get();
    }
}
