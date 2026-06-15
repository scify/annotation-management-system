<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Data\QuickLinkData;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Database\Seeder;

class DummyNotificationsSeeder extends Seeder {
    public function run(): void {
        $alice = User::query()->where('email', 'admin.alice@example.com')->firstOrFail();
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

        $service->markAsRead($carolToEvaReply, $eva->id);

        $service->createGenericNotification(
            recipientUserId: $frank->id,
            body: 'Hi Frank, please review the latest annotation guidelines before starting Batch 2.',
            senderUserId: $carol->id,
        );

        $service->createFlagNotification(
            recipientUserId: $carol->id,
            senderUserId: $alice->id,
            body: 'Instance #2 in Subproject Batch 1 has been flagged for review.',
            firstQuickLink: new QuickLinkData(
                label: 'Flagged Instance#2',
                url: 'projects/1/subprojects/1/edit',
            ),
            secondQuickLink: new QuickLinkData(
                label: 'Subproject Batch 1',
                url: 'projects/1/subprojects/1/edit',
            ),
        );

        $service->createInstanceRelatedNotification(
            recipientUserId: $carol->id,
            senderUserId: $alice->id,
            body: 'Instance #2 in Subproject Batch 1 has been updated.',
            firstQuickLink: new QuickLinkData(
                label: 'Instance#2',
                url: 'projects/1/subprojects/1/edit',
            ),
            secondQuickLink: new QuickLinkData(
                label: 'Subproject Batch 1',
                url: 'projects/1/subprojects/1/edit',
            ),
        );

        $service->createAnnouncementNotification(
            recipientUserIds: [$carol->id],
            senderUserId: $alice->id,
            body: 'Hello everyone!',
            quickLink: new QuickLinkData(
                label: 'Subproject Batch 1',
                url: 'projects/1/subprojects/1/edit',
            ),
        );

        $service->createProjectOwnershipNotification(
            recipientUserId: $carol->id,
            senderUserId: $alice->id,
            body: 'You have been assigned as owner of Project NER – English News.',
            quickLink: new QuickLinkData(
                label: 'Project NER – English News',
                url: 'projects/1',
            ),
        );

        $service->createProjectInvitationNotification(
            recipientUserId: $carol->id,
            senderUserId: $alice->id,
            body: 'You have been invited to collaborate on Project NER – English News.',
            quickLink: new QuickLinkData(
                label: 'Project NER – English News',
                url: 'projects/1',
            ),
        );

        $service->createWarningNotification(
            recipientUserIds: [$carol->id],
            title: 'Overdue Date Approaching',
            body: 'Subproject New Nov26 will surpass due date in 3 days',
        );

        $service->createInfoNotification(
            recipientUserIds: [$carol->id],
            title: 'Profile edit',
            body: '@admin_alice just edited your profile',
        );
    }
}
