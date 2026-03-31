<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRelationsEnum: string {
    case ANNOTATOR_OF_MANAGER = 'annotator of manager';

    case COLLABORATOR_OF_USER = 'collaborator of user';

    // extra helper to allow for greater customization of displayed values, without disclosing the name/value data directly
    public function label(): string {
        return match ($this) {
            self::ANNOTATOR_OF_MANAGER => 'annotator of manager',
            self::COLLABORATOR_OF_USER => 'collaborator of user',
        };
    }
}
