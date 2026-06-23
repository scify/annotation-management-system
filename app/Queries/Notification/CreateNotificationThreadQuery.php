<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Enums\NotificationThreadTypeEnum;
use App\Models\NotificationThread;

final readonly class CreateNotificationThreadQuery {
    public function create(
        NotificationThreadTypeEnum $type,
        ?string $title = null,
        ?int $projectId = null,
    ): NotificationThread {
        /** @var NotificationThread */
        return NotificationThread::query()->create([
            'type' => $type,
            'title' => $title,
            'project_id' => $projectId,
        ]);
    }
}
