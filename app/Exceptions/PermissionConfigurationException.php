<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class PermissionConfigurationException extends Exception {
    public static function configNotLoaded(): self {
        return new self('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
    }

    public static function teamForeignKeyNotLoaded(): self {
        return new self('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
    }

    public static function configNotFoundForDown(): self {
        return new self('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
    }
}
