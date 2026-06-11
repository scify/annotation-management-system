<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Database\Seeder;

class DummyNotificationsSeeder extends Seeder {
    public function run(): void {
        $eva = User::query()->where('email', 'annotator.eva@example.com')->firstOrFail();
        $carol = User::query()->where('email', 'manager.carol@example.com')->firstOrFail();
        $frank = User::query()->where('email', 'annotator.frank@example.com')->firstOrFail();

        $service = resolve(NotificationService::class);

        $evaToCarol = $service->createGenericNotification(
            recipientUserId: $carol->id,
            body: 'Hi Carol, I have a question about the annotation guidelines for Batch 1.',
            senderUserId: $eva->id,
        );

        $carolToEvaReply = $service->replyToGenericNotification(
            notificationThreadId: $evaToCarol->notification_thread_id,
            recipientUserId: $eva->id,
            body: 'Hi Eva, happy to help! Which part of the guidelines is unclear?',
            senderUserId: $carol->id,
        );

        $service->markAsRead($carolToEvaReply);

        $service->createGenericNotification(
            recipientUserId: $frank->id,
            body: 'Hi Frank, please review the latest annotation guidelines before starting Batch 2.',
            senderUserId: $carol->id,
        );
    }
}
