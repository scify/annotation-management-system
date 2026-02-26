<?php

declare(strict_types=1);

namespace App\Enums;

enum RolesEnum: string {
    case ADMIN = 'admin';

    case ANNOTATION_MANAGER = 'annotation-manager';

    case ANNOTATOR = 'annotator';

    // extra helper to allow for greater customization of displayed values, without disclosing the name/value data directly
    public function label(): string {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::ANNOTATION_MANAGER => 'Annotation Manager',
            self::ANNOTATOR => 'Annotator',
        };
    }
}
