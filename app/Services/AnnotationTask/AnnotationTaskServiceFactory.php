<?php

declare(strict_types=1);

namespace App\Services\AnnotationTask;

use App\Enums\AnnotationTaskTypeEnum;

final readonly class AnnotationTaskServiceFactory {
    public function make(AnnotationTaskTypeEnum $taskType): AnnotationTaskService {
        return match ($taskType) {
            AnnotationTaskTypeEnum::DUMMY => new DummyAnnotationTaskService(),
            AnnotationTaskTypeEnum::LEXICAL_SEMANTIC_CHANGE_DETECTION => new LexicalSemanticChangeDetectionAnnotationTaskService(),
        };
    }
}
