<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\ThreadMember;

final readonly class GetThreadRecipientsQuery {
    /**
     * Returns the usernames of all thread members except the initial sender.
     *
     * @return string[]
     */
    public function get(int $notificationThreadId, ?int $senderUserId): array {
        /** @var string[] */
        return ThreadMember::query()
            ->select('users.username')
            ->where('thread_members.notification_thread_id', $notificationThreadId)
            ->when($senderUserId !== null, fn ($q) => $q->where('thread_members.user_id', '!=', $senderUserId))
            ->join('users', 'thread_members.user_id', '=', 'users.id')
            ->distinct()
            ->pluck('username')
            ->all();
    }
}
