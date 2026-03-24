<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRelationsEnum: string {
    case ANNOTATOR_OF_ANNOTATION_POOL = 'annotator_of_annotation_pool';

    case COLLABORATOR_OF_USER = 'collaborator_of_user';

    // extra helper to allow for greater customization of displayed values, without disclosing the name/value data directly
    public function label(): string {
        return match ($this) {
            self::ANNOTATOR_OF_ANNOTATION_POOL => 'Annotation Pool',
            self::COLLABORATOR_OF_USER => 'Collaborator Of User',
        };
    }
}
