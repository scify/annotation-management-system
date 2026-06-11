<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\Notification;

final readonly class MarkNotificationAsReadQuery {
    public function mark(Notification $notification): void {
        if ($notification->is_read) {
            return;
        }

        $notification->update(['is_read' => true]);
    }
}
