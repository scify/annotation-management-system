<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationThreadResponseEnum: string {
    case ACCEPTED = 'accepted';

    case REJECTED = 'rejected';

    case UNREPLIED = 'unreplied';

    public function label(): string {
        return match ($this) {
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
            self::UNREPLIED => 'Unreplied',
        };
    }
}
