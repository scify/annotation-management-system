<?php

declare(strict_types=1);

namespace App\Enums;

enum AnnotationTaskTypeEnum: string {
    case DUMMY = 'dummy';
    case LEXICAL_SEMANTIC_CHANGE_DETECTION = 'lexical_semantic_change_detection';
}
