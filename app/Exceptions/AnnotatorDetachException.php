<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class AnnotatorDetachException extends RuntimeException implements PresentableError {
    public static function annotatorHasSubProjectAssignments(): self {
        return new self((string) __('projects.validation.annotator_has_subproject_assignments'));
    }

    public static function subProjectNotPending(): self {
        return new self((string) __('sub-projects.validation.subproject_not_pending'));
    }

    public function getUserMessage(): string {
        return $this->getMessage();
    }
}
