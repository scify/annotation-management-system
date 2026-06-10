<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class ProjectOwnershipException extends RuntimeException implements PresentableError {
    public static function ownershipAlreadyProposed(): self {
        return new self((string) __('projects.validation.ownership_already_proposed'));
    }

    public function getUserMessage(): string {
        return $this->getMessage();
    }
}
