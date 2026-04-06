<?php

declare(strict_types=1);

namespace App\Enums;

enum ProjectStatusEnum: string {
    case NOT_STARTED = 'not_started';

    case IN_PROGRESS = 'in_progress';

    case OVERDUE = 'overdue';

    case COMPLETED = 'completed';

    public function label(): string {
        return match ($this) {
            self::NOT_STARTED => 'Not Started',
            self::IN_PROGRESS => 'In Progress',
            self::OVERDUE => 'Overdue',
            self::COMPLETED => 'Completed',
        };
    }
}
