<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\QuickLink;

final readonly class CreateQuickLinkQuery {
    public function create(int $notificationThreadId, string $label, string $url): QuickLink {
        /** @var QuickLink */
        return QuickLink::query()->create([
            'notification_thread_id' => $notificationThreadId,
            'label' => $label,
            'url' => $url,
        ]);
    }
}
