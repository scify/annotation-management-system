<?php

declare(strict_types=1);

namespace App\Enums;

enum AnnotationStatusEnum: string {
    case SUBMITTED = 'submitted';
    case PENDING = 'pending';
    case NOT_ANNOTATED = 'not_annotated';

    public function label(): string {
        return match ($this) {
            self::SUBMITTED => 'submitted',
            self::PENDING => 'pending',
            self::NOT_ANNOTATED => 'not_annotated',
        };
    }
}
