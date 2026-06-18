<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\PresentableError;
use App\Http\Requests\Notification\ReplyNotificationRequest;
use App\Models\User;
use App\Services\Notification\NotificationsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller {
    public function __construct(
        private readonly NotificationsService $notificationService,
    ) {}

    public function markAsRead(int $notificationThreadId): RedirectResponse {
        /** @var User $user */
        $user = Auth::user();

        $this->notificationService->markAsRead($notificationThreadId, $user->id);

        return back();
    }

    public function markAsUnread(int $notificationThreadId): RedirectResponse {
        /** @var User $user */
        $user = Auth::user();

        $this->notificationService->markAsUnread($notificationThreadId, $user->id);

        return back();
    }

    public function markAllAsRead(): RedirectResponse {
        /** @var User $user */
        $user = Auth::user();

        $this->notificationService->markAllAsRead($user->id);

        return back();
    }

    public function approve(int $notificationThreadId): RedirectResponse {
        try {
            $this->notificationService->approve($notificationThreadId);
        } catch (PresentableError $presentableError) {
            return back()->with('error', $presentableError->getUserMessage());
        }

        return back()->with('success', __('notifications.action_approved'));
    }

    public function reject(int $notificationThreadId): RedirectResponse {
        try {
            $this->notificationService->reject($notificationThreadId);
        } catch (PresentableError $presentableError) {
            return back()->with('error', $presentableError->getUserMessage());
        }

        return back()->with('success', __('notifications.action_rejected'));
    }

    public function reply(ReplyNotificationRequest $request, int $notificationThreadId): RedirectResponse {
        /** @var User $user */
        $user = Auth::user();

        /** @var string $body */
        $body = $request->validated('body');

        $this->notificationService->reply(
            notificationThreadId: $notificationThreadId,
            senderUserId: $user->id,
            body: $body,
        );

        return back()->with('success', __('notifications.reply_sent'));
    }

    public function index(): Response {
        /** @var User $user */
        $user = Auth::user();

        $data = ['threads' => $this->notificationService->getMyNotifications($user->id)];

        $this->dumpDebugJson($data, 'notifications-index-data.json');

        return Inertia::render('notifications/index', $data);
    }
}
