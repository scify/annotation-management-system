<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class NotificationResponseException extends RuntimeException implements PresentableError {
    public static function responseNotFound(): self {
        return new self((string) __('notifications.errors.response_not_found'));
    }

    public static function cannotRejectAccepted(): self {
        return new self((string) __('notifications.errors.cannot_reject_accepted'));
    }

    public static function cannotApproveRejected(): self {
        return new self((string) __('notifications.errors.cannot_approve_rejected'));
    }

    public static function cannotRespondCancelled(): self {
        return new self((string) __('notifications.errors.cannot_respond_cancelled'));
    }

    public function getUserMessage(): string {
        return $this->getMessage();
    }
}
