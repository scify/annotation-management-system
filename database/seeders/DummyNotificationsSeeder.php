<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Data\QuickLinkData;
use App\Enums\NotificationThreadResponseEnum;
use App\Models\User;
use App\Services\Notification\AnnouncementNotificationService;
use App\Services\Notification\FlagNotificationService;
use App\Services\Notification\GenericNotificationService;
use App\Services\Notification\InfoNotificationService;
use App\Services\Notification\InstanceRelatedNotificationService;
use App\Services\Notification\NotificationsService;
use App\Services\Notification\ProjectInvitationNotificationService;
use App\Services\Notification\ProjectOwnershipNotificationService;
use App\Services\Notification\ProjectRequestToLeaveNotificationService;
use App\Services\Notification\WarningNotificationService;
use Illuminate\Database\Seeder;

class DummyNotificationsSeeder extends Seeder {
    public function run(): void {
        $alice = User::query()->where('email', 'admin.alice@example.com')->firstOrFail();
        $eva = User::query()->where('email', 'annotator.eva@example.com')->firstOrFail();
        $carol = User::query()->where('email', 'manager.carol@example.com')->firstOrFail();
        $dave = User::query()->where('email', 'manager.dave@example.com')->firstOrFail();
        $frank = User::query()->where('email', 'annotator.frank@example.com')->firstOrFail();
        $grace = User::query()->where('email', 'annotator.grace@example.com')->firstOrFail();
        $scifyManager = User::query()->where('email', 'annotation_manager@scify.org')->firstOrFail();

        $genericService = resolve(GenericNotificationService::class);
        $warningService = resolve(WarningNotificationService::class);
        $infoService = resolve(InfoNotificationService::class);
        $flagService = resolve(FlagNotificationService::class);
        $instanceRelatedService = resolve(InstanceRelatedNotificationService::class);
        $announcementService = resolve(AnnouncementNotificationService::class);
        $projectOwnershipService = resolve(ProjectOwnershipNotificationService::class);
        $projectInvitationService = resolve(ProjectInvitationNotificationService::class);
        $projectRequestToLeaveService = resolve(ProjectRequestToLeaveNotificationService::class);
        $service = resolve(NotificationsService::class);

        $evaToCarol = $genericService->createNotification(
            recipientUserId: $carol->id,
            body: 'Hi Carol, I have a question about the annotation guidelines for Batch 1.',
            senderUserId: $eva->id,
        );

        $carolToEvaReply = $genericService->reply(
            notificationThreadId: $evaToCarol->notification_thread_id,
            senderUserId: $carol->id,
            body: 'Hi Eva, happy to help! Which part of the guidelines is unclear?',
        );

        $service->markAsRead($carolToEvaReply->notification_thread_id, $eva->id);

        $genericService->createNotification(
            recipientUserId: $frank->id,
            body: 'Hi Frank, please review the latest annotation guidelines before starting Batch 2.',
            senderUserId: $carol->id,
        );

        $flagService->createNotification(
            recipientUserIds: [$carol->id],
            body: 'Instance #2 in Subproject Batch 1 has been flagged for review.',
            senderUserId: $alice->id,
            firstQuickLink: new QuickLinkData(
                label: 'Flagged Instance#2',
                url: 'projects/1/subprojects/1/edit',
            ),
            secondQuickLink: new QuickLinkData(
                label: 'Subproject Batch 1',
                url: 'projects/1/subprojects/1/edit',
            ),
        );

        $instanceRelatedService->createNotification(
            recipientUserIds: [$carol->id],
            body: 'Instance #2 in Subproject Batch 1 has been updated.',
            senderUserId: $alice->id,
            firstQuickLink: new QuickLinkData(
                label: 'Instance#2',
                url: 'projects/1/subprojects/1/edit',
            ),
            secondQuickLink: new QuickLinkData(
                label: 'Subproject Batch 1',
                url: 'projects/1/subprojects/1/edit',
            ),
        );

        $announcementService->createNotification(
            recipientUserIds: [$carol->id, $dave->id, $eva->id, $grace->id],
            body: 'Hello everyone!',
            senderUserId: $alice->id,
            quickLink: new QuickLinkData(
                label: 'Subproject Batch 1',
                url: 'projects/1/subprojects/1/edit',
            ),
        );

        $projectOwnershipService->createNotification(
            recipientUserId: $carol->id,
            senderUserId: $alice->id,
            body: 'You have been assigned as owner of Project NER – English News.',
            quickLink: new QuickLinkData(
                label: 'Project NER – English News',
                url: 'projects/1',
            ),
        );

        $projectInvitationService->createNotification(
            recipientUserId: $carol->id,
            senderUserId: $alice->id,
            body: 'You have been invited to collaborate on Project NER – English News.',
            quickLink: new QuickLinkData(
                label: 'Project NER – English News',
                url: 'projects/1',
            ),
        );

        $acceptedInvitation = $projectInvitationService->createNotification(
            recipientUserId: $carol->id,
            senderUserId: $alice->id,
            body: 'You have been invited to collaborate on Project Sentiment Analysis.',
            quickLink: new QuickLinkData(
                label: 'Project Sentiment Analysis',
                url: 'projects/2',
            ),
        );
        $acceptedInvitation->thread->response?->update(['response' => NotificationThreadResponseEnum::ACCEPTED]);

        $rejectedOwnership = $projectOwnershipService->createNotification(
            recipientUserId: $carol->id,
            senderUserId: $alice->id,
            body: 'You have been assigned as owner of Project Sentiment Analysis.',
            quickLink: new QuickLinkData(
                label: 'Project Sentiment Analysis',
                url: 'projects/2',
            ),
        );
        $rejectedOwnership->thread->response?->update(['response' => NotificationThreadResponseEnum::REJECTED]);

        $projectRequestToLeaveService->createNotification(
            recipientUserId: $carol->id,
            senderUserId: $dave->id,
            body: 'You have been asked to leave Project NER – English News.',
            quickLink: new QuickLinkData(
                label: 'Project NER – English News',
                url: 'projects/1',
            ),
        );

        $acceptedLeaveRequest = $projectRequestToLeaveService->createNotification(
            recipientUserId: $carol->id,
            senderUserId: $scifyManager->id,
            body: 'You have been asked to leave Project NER – English News.',
            quickLink: new QuickLinkData(
                label: 'Project NER – English News',
                url: 'projects/1',
            ),
        );
        $acceptedLeaveRequest->thread->response?->update(['response' => NotificationThreadResponseEnum::ACCEPTED]);

        $rejectedLeaveRequest = $projectRequestToLeaveService->createNotification(
            recipientUserId: $carol->id,
            senderUserId: $alice->id,
            body: 'You have been asked to leave Project NER – English News.',
            quickLink: new QuickLinkData(
                label: 'Project NER – English News',
                url: 'projects/1',
            ),
        );
        $rejectedLeaveRequest->thread->response?->update(['response' => NotificationThreadResponseEnum::REJECTED]);

        $warningService->createNotification(
            recipientUserId: $carol->id,
            body: 'Subproject New Nov26 will surpass due date in 3 days',
            title: 'Overdue Date Approaching',
        );

        $infoService->createNotification(
            recipientUserId: $carol->id,
            body: '@admin_alice just edited your profile',
            title: 'Profile edit',
        );
    }
}
