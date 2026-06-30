<?php

declare(strict_types=1);

namespace Database\Seeders\Dummy;

use App\Data\QuickLinkData;
use App\Data\TranslatableMessage;
use App\Enums\NotificationThreadResponseEnum;
use App\Models\Annotation;
use App\Models\Project;
use App\Models\SubProject;
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

        $nerProject = Project::query()->where('name', 'NER – English News')->firstOrFail();
        $sentimentProject = Project::query()->where('name', 'Sentiment – Product Reviews')->firstOrFail();

        $batch1 = SubProject::query()->where('name', 'Batch 1')->firstOrFail();
        $batch1Annotation = Annotation::query()
            ->join('annotation_assignments', 'annotation_assignments.id', '=', 'annotations.annotation_assignment_id')
            ->where('annotation_assignments.sub_project_id', $batch1->id)
            ->orderBy('annotations.id')
            ->select('annotations.id', 'annotations.annotation_assignment_id')
            ->first();

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
            body: 'An instance in Subproject Batch 1 has been flagged for review.',
            senderUserId: $alice->id,
            firstQuickLink: new QuickLinkData(
                label: 'Flagged Instance#',
                url: 'subprojects/' . $batch1->id . '/annotation',
                annotationId: $batch1Annotation?->id,
            ),
            secondQuickLink: new QuickLinkData(
                label: 'Subproject Batch 1',
                url: 'projects/1/subprojects/1/edit',
            ),
        );

        $instanceRelatedService->createNotification(
            recipientUserIds: [$carol->id],
            body: 'An instance in Subproject Batch 1 has been updated.',
            senderUserId: $alice->id,
            firstQuickLink: new QuickLinkData(
                label: 'Instance#',
                url: 'subprojects/' . $batch1->id . '/annotation',
                annotationId: $batch1Annotation?->id,
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
            body: TranslatableMessage::encode('notifications.messages.project_ownership', ['project' => $nerProject->name, 'recipient' => $carol->username]),
            quickLink: new QuickLinkData(
                label: $nerProject->name,
                url: 'projects/' . $nerProject->id,
            ),
            projectId: $nerProject->id,
        );

        $projectInvitationService->createNotification(
            recipientUserId: $carol->id,
            senderUserId: $alice->id,
            body: TranslatableMessage::encode('notifications.messages.project_invitation', ['project' => $nerProject->name, 'recipient' => $carol->username]),
            quickLink: new QuickLinkData(
                label: $nerProject->name,
                url: 'projects/' . $nerProject->id,
            ),
            projectId: $nerProject->id,
        );

        $acceptedInvitation = $projectInvitationService->createNotification(
            recipientUserId: $carol->id,
            senderUserId: $alice->id,
            body: TranslatableMessage::encode('notifications.messages.project_invitation', ['project' => $sentimentProject->name, 'recipient' => $carol->username]),
            quickLink: new QuickLinkData(
                label: $sentimentProject->name,
                url: 'projects/' . $sentimentProject->id,
            ),
            projectId: $sentimentProject->id,
        );
        $acceptedInvitation->thread->response?->update(['response' => NotificationThreadResponseEnum::ACCEPTED]);

        $rejectedOwnership = $projectOwnershipService->createNotification(
            recipientUserId: $carol->id,
            senderUserId: $alice->id,
            body: TranslatableMessage::encode('notifications.messages.project_ownership', ['project' => $sentimentProject->name, 'recipient' => $carol->username]),
            quickLink: new QuickLinkData(
                label: $sentimentProject->name,
                url: 'projects/' . $sentimentProject->id,
            ),
            projectId: $sentimentProject->id,
        );
        $rejectedOwnership->thread->response?->update(['response' => NotificationThreadResponseEnum::REJECTED]);

        $projectRequestToLeaveService->createNotification(
            recipientUserId: $carol->id,
            senderUserId: $dave->id,
            body: TranslatableMessage::encode('notifications.messages.project_request_to_leave', ['project' => $nerProject->name]),
            quickLink: new QuickLinkData(
                label: $nerProject->name,
                url: 'projects/' . $nerProject->id,
            ),
            projectId: $nerProject->id,
        );

        $acceptedLeaveRequest = $projectRequestToLeaveService->createNotification(
            recipientUserId: $carol->id,
            senderUserId: $scifyManager->id,
            body: TranslatableMessage::encode('notifications.messages.project_request_to_leave', ['project' => $nerProject->name]),
            quickLink: new QuickLinkData(
                label: $nerProject->name,
                url: 'projects/' . $nerProject->id,
            ),
            projectId: $nerProject->id,
        );
        $acceptedLeaveRequest->thread->response?->update(['response' => NotificationThreadResponseEnum::ACCEPTED]);

        $rejectedLeaveRequest = $projectRequestToLeaveService->createNotification(
            recipientUserId: $carol->id,
            senderUserId: $alice->id,
            body: TranslatableMessage::encode('notifications.messages.project_request_to_leave', ['project' => $nerProject->name]),
            quickLink: new QuickLinkData(
                label: $nerProject->name,
                url: 'projects/' . $nerProject->id,
            ),
            projectId: $nerProject->id,
        );
        $rejectedLeaveRequest->thread->response?->update(['response' => NotificationThreadResponseEnum::REJECTED]);

        $projectRequestToLeaveService->createNotification(
            recipientUserId: $alice->id,
            senderUserId: $carol->id,
            body: TranslatableMessage::encode('notifications.messages.project_request_to_leave', ['project' => $sentimentProject->name]),
            quickLink: new QuickLinkData(
                label: $sentimentProject->name,
                url: 'projects/' . $sentimentProject->id,
            ),
            projectId: $sentimentProject->id,
        );

        $warningService->createNotification(
            recipientUserId: $carol->id,
            body: TranslatableMessage::encode('notifications.messages.overdue_approaching.body', ['subproject' => 'New Nov26', 'days' => 3]),
            title: TranslatableMessage::encode('notifications.messages.overdue_approaching.title'),
        );

        $infoService->createNotification(
            recipientUserId: $carol->id,
            body: TranslatableMessage::encode('notifications.messages.profile_edited.body', ['editor' => 'admin_alice', 'recipient' => $carol->username]),
            title: TranslatableMessage::encode('notifications.messages.profile_edited.title'),
        );

        $canceledOwnership = $projectOwnershipService->createNotification(
            recipientUserId: $dave->id,
            senderUserId: $carol->id,
            body: TranslatableMessage::encode('notifications.messages.project_ownership', ['project' => $nerProject->name, 'recipient' => $dave->username]),
            quickLink: new QuickLinkData(
                label: $nerProject->name,
                url: 'projects/' . $nerProject->id,
            ),
            projectId: $nerProject->id,
        );
        $canceledOwnership->thread->response?->update(['response' => NotificationThreadResponseEnum::CANCELED]);

        $infoService->notifyRemovedManager($nerProject->id, $dave->id);
        $infoService->notifyCancelledOwnershipProposal($nerProject->id, $dave->id, $carol->id);
        $infoService->notifyCancelledLeaveRequest($nerProject->id, $dave->id);
        $infoService->notifyOwnerOfAcceptedOwnership($nerProject->id, $carol->id, $dave->id);
        $infoService->notifyOwnerOfRejectedOwnership($sentimentProject->id, $dave->id);
        $infoService->notifyLeaveRequestAccepted($nerProject->id, $dave->id);
        $infoService->notifyLeaveRequestRejected($sentimentProject->id, $scifyManager->id);
        $infoService->notifyManagersAboutNewAnnotatorsOfProject($nerProject->id, [$eva->id, $frank->id]);
    }
}
