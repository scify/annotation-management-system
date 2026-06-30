<?php

declare(strict_types=1);

namespace App\Queries\Annotation;

use App\Models\Annotation;

final readonly class GetAnnotationByIdQuery {
    public function get(int $id): Annotation {
        /** @var Annotation */
        return Annotation::query()
            ->select(['id', 'annotator_instance_index', 'dataset_instance_id'])
            ->with('datasetInstance')
            ->findOrFail($id);
    }

    public function getAnnotationData(int $id): Annotation {
        /** @var Annotation */
        return Annotation::query()
            ->select(['id', 'annotations', 'confidence', 'flag_notification_thread_id'])
            ->findOrFail($id);
    }
}
