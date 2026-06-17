<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Models\NotificationThread;

abstract class AbstractNotificationService {
    abstract protected function setTitle(NotificationThread $thread, int $userId): void;

    abstract protected function setTopRight(NotificationThread $thread): void;

    abstract protected function allowsReply(): bool;

    final public function augmentNotification(NotificationThread $thread, int $userId): void {
        $this->setResponse($thread);
        $this->setTopRight($thread);
        $this->setTitle($thread, $userId);
        $thread->setAttribute('allowed_to_reply', $this->allowsReply());
    }

    protected function setResponse(NotificationThread $thread): void {
        $thread->unsetRelation('response');
    }
}
