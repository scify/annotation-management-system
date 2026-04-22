<?php

declare(strict_types=1);

namespace App\Enums;

enum SubProjectPriorityEnum: string {
    case HIGH = 'high';

    case MEDIUM = 'medium';

    case LOW = 'low';

    public function label(): string {
        return match ($this) {
            self::HIGH => 'High',
            self::MEDIUM => 'Medium',
            self::LOW => 'Low',
        };
    }
}
