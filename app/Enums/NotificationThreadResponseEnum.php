<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationThreadResponseEnum: string {
    case ACCEPTED = 'accepted';

    case CANCELED = 'canceled';

    case REJECTED = 'rejected';

    case UNREPLIED = 'unreplied';

    public function label(): string {
        return match ($this) {
            self::ACCEPTED => 'Accepted',
            self::CANCELED => 'Cancelled',
            self::REJECTED => 'Rejected',
            self::UNREPLIED => 'Unreplied',
        };
    }
}
