<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class UserCreationException extends RuntimeException implements PresentableError {
    public static function duplicateName(): self {
        return new self((string) __('users.validation.name_taken'));
    }

    public static function duplicateUsername(): self {
        return new self((string) __('users.validation.username_taken'));
    }

    public static function duplicateEmail(): self {
        return new self((string) __('users.validation.email_taken'));
    }

    public static function passwordMismatch(): self {
        return new self((string) __('users.validation.password_mismatch'));
    }

    public function getUserMessage(): string {
        return $this->getMessage();
    }
}
