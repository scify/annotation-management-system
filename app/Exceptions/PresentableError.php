<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Marks an exception as safe to display to the end user.
 *
 * Controllers can catch (PresentableError $e) broadly, without coupling
 * to concrete exception classes, and surface $e->getUserMessage() in
 * flash messages or JSON responses.
 */
interface PresentableError {
    public function getUserMessage(): string;
}
