<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a newly-created ADMIN or ANNOTATION_MANAGER user, welcoming them to
 * the platform and naming the user who created their account.
 *
 * Queued via ShouldQueue: in development (QUEUE_CONNECTION=sync) it is delivered
 * synchronously during the request; in production (QUEUE_CONNECTION=database) it
 * is processed by a queue worker.
 */
final class UserWelcomeNotification extends Notification implements ShouldQueue {
    use Queueable;

    public function __construct(
        private readonly ?User $creator,
        private readonly RolesEnum $role,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage {
        $creatorName = $this->creator instanceof User
            ? $this->creator->name
            : __('emails.welcome.creator_fallback');

        $creatorEmail = $this->creator instanceof User
            ? $this->creator->email
            : __('emails.welcome.creator_fallback');

        /** @var User $notifiable */
        return new MailMessage()
            ->subject(__('emails.welcome.subject', ['app' => __('common.app_name')]))
            ->view('emails.users.welcome', [
                'name' => $notifiable->name,
                'creatorName' => $creatorName,
                'creatorEmail' => $creatorEmail,
                'roleName' => __('roles.' . $this->role->value),
                'appName' => __('common.app_name'),
                'logoUrl' => asset('images/logo.png'),
                'actionUrl' => route('login'),
            ]);
    }
}
