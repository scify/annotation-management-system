<?php

declare(strict_types=1);

namespace App\Enums;

enum AgreementEnum: string {
    case HIGH = 'high';

    case LOW = 'low';

    case MEDIUM = 'medium';

    case UNDEFINED = 'undefined';

    public function label(): string {
        return match ($this) {
            self::HIGH => 'High',
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::UNDEFINED => 'Undefined',
        };
    }
}
