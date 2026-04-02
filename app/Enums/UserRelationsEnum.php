<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRelationsEnum: string {
    case ANNOTATOR_OF_MANAGER = 'annotator_of_manager';

    case COLLABORATOR_OF_USER = 'collaborator_of_user';

    // extra helper to allow for greater customization of displayed values, without disclosing the name/value data directly
    public function label(): string {
        return match ($this) {
            self::ANNOTATOR_OF_MANAGER => 'annotator_of_manager',
            self::COLLABORATOR_OF_USER => 'collaborator_of_user',
        };
    }
}
