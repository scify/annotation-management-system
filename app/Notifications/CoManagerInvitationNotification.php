<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to an existing co-manager (ADMIN or ANNOTATION_MANAGER) when they are invited to
 * co-manage a project. Complements the in-app accept/reject notification.
 *
 * Queued via ShouldQueue: in development (QUEUE_CONNECTION=sync) it is delivered
 * synchronously during the request; in production (QUEUE_CONNECTION=database) it
 * is processed by a queue worker.
 */
final class CoManagerInvitationNotification extends Notification implements ShouldQueue {
    use Queueable;

    public function __construct(
        private readonly string $projectName,
        private readonly string $inviterName,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage {
        /** @var User $notifiable */
        return new MailMessage()
            ->subject(__('emails.co_manager_invitation.subject', ['app' => __('common.app_name')]))
            ->view('emails.invitations.co_manager', [
                'name' => $notifiable->name,
                'inviterName' => $this->inviterName,
                'projectName' => $this->projectName,
                'appName' => __('common.app_name'),
                'logoUrl' => asset('images/logo.png'),
                'actionUrl' => route('login'),
            ]);
    }
}
