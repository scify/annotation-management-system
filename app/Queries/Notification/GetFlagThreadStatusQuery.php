<?php

declare(strict_types=1);

namespace App\Queries\Notification;

use App\Models\ThreadMember;

final readonly class GetFlagThreadStatusQuery {
    /**
     * @return array{is_replied: bool, is_reply_read: bool}
     */
    public function get(int $flagNotificationThreadId, int $creatorUserId): array {
        $members = ThreadMember::query()
            ->where('notification_thread_id', $flagNotificationThreadId)
            ->get(['user_id', 'is_read']);

        $isReplied = $members->contains(fn (ThreadMember $m): bool => $m->user_id !== $creatorUserId);
        /** @var ThreadMember|null $creatorMember */
        $creatorMember = $members->firstWhere('user_id', $creatorUserId);

        return [
            'is_replied' => $isReplied,
            'is_reply_read' => $creatorMember !== null && $creatorMember->is_read,
        ];
    }
}
