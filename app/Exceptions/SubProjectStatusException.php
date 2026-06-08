<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\ProjectStatusEnum;
use RuntimeException;

final class SubProjectStatusException extends RuntimeException implements PresentableError {
    public function __construct(private readonly string $translationKey, string $debugMessage = '') {
        parent::__construct($debugMessage !== '' ? $debugMessage : $translationKey);
    }

    public static function projectNotInProgress(): self {
        return new self(
            'sub-projects.messages.project_not_in_progress',
            'Cannot change subproject status: parent project is not in_progress',
        );
    }

    public static function invalidTransition(ProjectStatusEnum $from, ProjectStatusEnum $to): self {
        return new self(
            'sub-projects.messages.invalid_status_transition',
            sprintf('Invalid subproject status transition: %s → %s', $from->value, $to->value),
        );
    }

    public function getUserMessage(): string {
        return __($this->translationKey);
    }
}
