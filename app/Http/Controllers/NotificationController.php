<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\PresentableError;
use App\Http\Requests\Notification\ReplyNotificationRequest;
use App\Http\Requests\Notification\SendMessageRequest;
use App\Models\User;
use App\Services\Notification\GenericNotificationService;
use App\Services\Notification\NotificationsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller {
    public function __construct(
        private readonly NotificationsService $notificationService,
        private readonly GenericNotificationService $genericNotificationService,
    ) {}

    public function markAsRead(int $notificationThreadId): JsonResponse {
        /** @var User $user */
        $user = Auth::user();

        $this->notificationService->markAsRead($notificationThreadId, $user->id);

        return $this->jsonSuccess();
    }

    public function markAsUnread(int $notificationThreadId): JsonResponse {
        /** @var User $user */
        $user = Auth::user();

        $this->notificationService->markAsUnread($notificationThreadId, $user->id);

        return $this->jsonSuccess();
    }

    public function markAllAsRead(): JsonResponse {
        /** @var User $user */
        $user = Auth::user();

        $this->notificationService->markAllAsRead($user->id);

        return $this->jsonSuccess();
    }

    public function approve(int $notificationThreadId): JsonResponse {
        try {
            $this->notificationService->approve($notificationThreadId);
        } catch (PresentableError $presentableError) {
            return $this->jsonError($presentableError->getUserMessage());
        }

        return $this->jsonSuccess(__('notifications.action_approved'));
    }

    public function reject(int $notificationThreadId): JsonResponse {
        try {
            $this->notificationService->reject($notificationThreadId);
        } catch (PresentableError $presentableError) {
            return $this->jsonError($presentableError->getUserMessage());
        }

        return $this->jsonSuccess(__('notifications.action_rejected'));
    }

    public function reply(ReplyNotificationRequest $request, int $notificationThreadId): JsonResponse {
        /** @var User $user */
        $user = Auth::user();

        /** @var string $body */
        $body = $request->validated('body');

        $notification = $this->notificationService->reply(
            notificationThreadId: $notificationThreadId,
            senderUserId: $user->id,
            body: $body,
        );

        return $this->jsonSuccess(__('notifications.reply_sent'), [
            'notification' => $this->notificationService->presentNotification($notification),
        ]);
    }

    public function sendMessage(SendMessageRequest $request): JsonResponse {
        /** @var User $user */
        $user = Auth::user();

        $this->genericNotificationService->createNotification(
            recipientUserId: $request->integer('recipient_user_id'),
            body: $request->string('body')->trim()->value(),
            senderUserId: $user->id,
        );

        return $this->jsonSuccess();
    }

    public function index(): Response {
        /** @var User $user */
        $user = Auth::user();

        $data = ['threads' => $this->notificationService->getMyNotifications($user->id)];

        $this->dumpDebugJson($data, 'notifications-index-data.json');

        return Inertia::render('notifications/index', $data);
    }
}
