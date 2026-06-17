<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\ThreadMember;

final readonly class CreateThreadMemberQuery {
    public function create(int $notificationThreadId, int $userId, bool $isRead = false): ThreadMember {
        /** @var ThreadMember */
        return ThreadMember::query()->create([
            'notification_thread_id' => $notificationThreadId,
            'user_id' => $userId,
            'is_read' => $isRead,
        ]);
    }

    /** @param int[] $userIds */
    public function createBatch(int $notificationThreadId, array $userIds, bool $isRead = false): void {
        $now = now()->toDateTimeString();

        ThreadMember::query()->insert(
            array_map(fn (int $userId): array => [
                'notification_thread_id' => $notificationThreadId,
                'user_id' => $userId,
                'is_read' => $isRead,
                'created_at' => $now,
                'updated_at' => $now,
            ], $userIds)
        );
    }
}
