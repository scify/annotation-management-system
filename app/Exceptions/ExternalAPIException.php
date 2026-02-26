<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class ExternalAPIException extends Exception implements PresentableError {
    private const string DEFAULT_USER_MESSAGE = 'An error occurred while communicating with an external service.';

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null) {
        if (app()->environment('production')) {
            $message = self::DEFAULT_USER_MESSAGE;
        }

        parent::__construct($message, $code, $previous);
    }

    public function getUserMessage(): string {
        return app()->environment('production')
            ? self::DEFAULT_USER_MESSAGE
            : $this->getMessage();
    }
}
