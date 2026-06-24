<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\NotificationResponseException;
use App\Exceptions\PresentableError;
use App\Http\Requests\Notification\ReplyNotificationRequest;
use App\Http\Requests\Notification\SendAnnouncementRequest;
use App\Http\Requests\Notification\SendMessageRequest;
use App\Models\User;
use App\Services\Notification\AnnouncementNotificationService;
use App\Services\Notification\GenericNotificationService;
use App\Services\Notification\NotificationsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller {
    public function __construct(
        private readonly NotificationsService $notificationService,
        private readonly GenericNotificationService $genericNotificationService,
        private readonly AnnouncementNotificationService $announcementNotificationService,
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
        } catch (NotificationResponseException $e) {
            return $this->jsonError($e->getUserMessage(), code: $e->errorCode());
        } catch (PresentableError $presentableError) {
            return $this->jsonError($presentableError->getUserMessage());
        }

        return $this->jsonSuccess(__('notifications.action_approved'));
    }

    public function reject(int $notificationThreadId): JsonResponse {
        try {
            $this->notificationService->reject($notificationThreadId);
        } catch (NotificationResponseException $e) {
            return $this->jsonError($e->getUserMessage(), code: $e->errorCode());
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

    public function sendAnnouncement(SendAnnouncementRequest $request): JsonResponse {
        /** @var User $user */
        $user = Auth::user();

        $this->announcementNotificationService->notifyProjectMembers(
            projectId: $request->integer('project_id'),
            subProjectId: $request->filled('sub_project_id') ? $request->integer('sub_project_id') : null,
            body: $request->string('body')->trim()->value(),
            senderUserId: $user->id,
        );

        return $this->jsonSuccess();
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

        $response = Inertia::render('notifications/index', $data)->toResponse(request());
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
