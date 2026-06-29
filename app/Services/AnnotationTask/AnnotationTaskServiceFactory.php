<?php

declare(strict_types=1);

namespace App\Services\AnnotationTask;

use App\Enums\AnnotationTaskTypeEnum;
use App\Queries\Dataset\GetDatasetInstanceQuery;
use App\Queries\SubProject\GetSubProjectByIdQuery;

final readonly class AnnotationTaskServiceFactory {
    public function __construct(
        private GetDatasetInstanceQuery $datasetInstanceQuery,
        private GetSubProjectByIdQuery $subProjectByIdQuery,
    ) {}

    public function make(AnnotationTaskTypeEnum $taskType): AnnotationTaskService {
        return match ($taskType) {
            AnnotationTaskTypeEnum::DUMMY => new DummyAnnotationTaskService($this->datasetInstanceQuery),
            AnnotationTaskTypeEnum::LEXICAL_SEMANTIC_CHANGE_DETECTION => new LexicalSemanticChangeDetectionAnnotationTaskService($this->datasetInstanceQuery, $this->subProjectByIdQuery),
        };
    }
}
