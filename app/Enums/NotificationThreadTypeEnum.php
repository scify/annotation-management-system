<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationThreadTypeEnum: string {
    case FLAG_NOTIFICATION = 'flag_notification';

    case INSTANCE_RELATED = 'instance_related';

    case GENERIC = 'generic';

    case INFO = 'info';

    case WARNING = 'warning';

    case PROJECT_OWNERSHIP = 'project_ownership';

    case PROJECT_INVITATION = 'project_invitation';

    case PROJECT_REQUEST_TO_LEAVE = 'project_request_to_leave';

    case ANNOUNCEMENT = 'announcement';

    public function label(): string {
        return match ($this) {
            self::FLAG_NOTIFICATION => 'Flag Notification',
            self::INSTANCE_RELATED => 'Instance Related',
            self::GENERIC => 'Generic',
            self::INFO => 'Info',
            self::WARNING => 'Warning',
            self::PROJECT_OWNERSHIP => 'Ownership',
            self::PROJECT_INVITATION => 'Invitation to Project',
            self::PROJECT_REQUEST_TO_LEAVE => 'Request to Leave',
            self::ANNOUNCEMENT => 'Announcement',
        };
    }
}
