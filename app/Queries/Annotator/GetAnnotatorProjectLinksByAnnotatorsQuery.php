<?php

declare(strict_types=1);

namespace App\Queries\Annotator;

use App\Models\AnnotatorOfProject;
use Illuminate\Database\Eloquent\Collection;

final readonly class GetAnnotatorProjectLinksByAnnotatorsQuery {
    /**
     * @param  array<int, mixed>  $annotatorIds
     *
     * @return Collection<int, AnnotatorOfProject>
     */
    public function get(array $annotatorIds): Collection {
        if ($annotatorIds === []) {
            return new Collection();
        }

        return AnnotatorOfProject::query()
            ->whereIn('user_id', $annotatorIds)
            ->get();
    }
}
