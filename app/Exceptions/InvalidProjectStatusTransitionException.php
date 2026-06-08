<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\ProjectStatusEnum;
use RuntimeException;

final class InvalidProjectStatusTransitionException extends RuntimeException implements PresentableError {
    public function __construct(ProjectStatusEnum $from, ProjectStatusEnum $to) {
        parent::__construct(sprintf('Invalid status transition: %s → %s', $from->value, $to->value));
    }

    public function getUserMessage(): string {
        return __('projects.messages.invalid_status_transition');
    }
}
