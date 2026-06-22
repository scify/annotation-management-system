<?php

declare(strict_types=1);

use App\Data\QuickLinkData;
use App\Enums\NotificationThreadResponseEnum;
use App\Enums\RolesEnum;
use App\Models\NotificationThreadResponse;
use App\Models\Project;
use App\Models\User;
use App\Services\Notification\AnnouncementNotificationService;
use App\Services\Notification\FlagNotificationService;
use App\Services\Notification\GenericNotificationService;
use App\Services\Notification\ProjectInvitationNotificationService;
use App\Services\Notification\ProjectOwnershipNotificationService;
use App\Services\Notification\WarningNotificationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

describe('Notifications page', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('redirects unauthenticated users to the login page', function (): void {
        visit(route('notifications.index'))
            ->assertRoute('login');
    });

    it('shows the different notification types in the list', function (): void {
        // Arrange
        $recipient = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ANNOTATOR);
        $sender = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER);

        resolve(GenericNotificationService::class)->createNotification(
            $recipient->id,
            'Question about the annotation guidelines for Batch 1.',
            $sender->id,
        );
        resolve(WarningNotificationService::class)->createNotification(
            $recipient->id,
            'Subproject Nov26 will surpass due date in 3 days.',
            'Overdue Date Approaching',
        );
        resolve(FlagNotificationService::class)->createNotification(
            recipientUserIds: [$recipient->id],
            body: 'Instance #2 has been flagged for review.',
            senderUserId: $sender->id,
            firstQuickLink: new QuickLinkData('Flagged Instance#2', 'projects/1/subprojects/1/edit'),
            secondQuickLink: new QuickLinkData('Subproject Batch 1', 'projects/1/subprojects/1/edit'),
        );
        resolve(AnnouncementNotificationService::class)->createNotification(
            recipientUserIds: [$recipient->id],
            body: 'We have to speed up our work.',
            senderUserId: $sender->id,
            quickLink: new QuickLinkData('Subproject Batch 1', 'projects/1/subprojects/1/edit'),
        );

        // Act + Assert
        loginViaForm($recipient->username)
            ->navigate(route('notifications.index'))
            ->assertSee('Notifications')
            ->assertSee('Question about the annotation guidelines for Batch 1.')
            ->assertSee('Overdue Date Approaching')
            ->assertSee('Subproject Nov26 will surpass due date in 3 days.')
            ->assertSee('Instance #2 has been flagged for review.')
            ->assertSee('Flagged Instance#2')
            ->assertSee('We have to speed up our work.')
            ->assertNoJavascriptErrors();
    });

    it('opens a conversation thread and shows the reply box', function (): void {
        $recipient = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ANNOTATOR);
        $sender = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER);

        resolve(GenericNotificationService::class)->createNotification(
            $recipient->id,
            'Please review the latest annotation guidelines.',
            $sender->id,
        );

        loginViaForm($recipient->username)
            ->navigate(route('notifications.index'))
            ->assertSee('Please review the latest annotation guidelines.')
            ->click('button:has-text("Please review the latest annotation guidelines.")')
            ->wait(0.2)
            ->assertSee('Send Reply')
            ->assertNoJavascriptErrors();
    });

    it('shows approve and reject for an unreplied action thread', function (): void {
        $recipient = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ANNOTATOR);
        $sender = User::factory()->create()->assignRole(RolesEnum::ADMIN);
        $project = Project::factory()->create(['owner_user_id' => $sender->id]);

        resolve(ProjectOwnershipNotificationService::class)->createNotification(
            $recipient->id,
            $sender->id,
            'You have been assigned as owner of Project NER.',
            new QuickLinkData('Project NER', 'projects/1'),
            $project->id,
            $recipient->id,
        );

        loginViaForm($recipient->username)
            ->navigate(route('notifications.index'))
            ->assertSee('You have been assigned as owner of Project NER.')
            ->click('button:has-text("You have been assigned as owner of Project NER.")')
            ->wait(0.2)
            ->assertSee('Accept')
            ->assertSee('Reject')
            ->assertNoJavascriptErrors();
    });

    it('shows a status line instead of buttons for a decided action thread', function (): void {
        $recipient = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ANNOTATOR);
        $sender = User::factory()->create()->assignRole(RolesEnum::ADMIN);
        $project = Project::factory()->create(['owner_user_id' => $sender->id]);

        $notification = resolve(ProjectInvitationNotificationService::class)->createNotification(
            $recipient->id,
            $sender->id,
            'You have been invited to collaborate on Project Sentiment Analysis.',
            new QuickLinkData('Project Sentiment Analysis', 'projects/2'),
            $project->id,
            $recipient->id,
        );

        // Mark the invitation as accepted so the detail pane shows the status line.
        NotificationThreadResponse::query()
            ->where('notification_thread_id', $notification->notification_thread_id)
            ->update(['response' => NotificationThreadResponseEnum::ACCEPTED]);

        loginViaForm($recipient->username)
            ->navigate(route('notifications.index'))
            ->assertSee('You have been invited to collaborate on Project Sentiment Analysis.')
            ->click('button:has-text("You have been invited to collaborate on Project Sentiment Analysis.")')
            ->wait(0.2)
            ->assertSee('Accepted')
            ->assertDontSee('Accept')
            ->assertNoJavascriptErrors();
    });
});
