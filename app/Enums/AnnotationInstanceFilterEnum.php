<?php

declare(strict_types=1);

namespace App\Enums;

enum AnnotationInstanceFilterEnum: string {
    case All = 'all';
    case NotAnnotated = 'not_annotated';
    case Pending = 'pending';
    case Submitted = 'submitted';
}
