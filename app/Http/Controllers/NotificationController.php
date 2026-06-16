<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller {
    public function __construct(
        private readonly NotificationService $notificationService,
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

    public function index(): Response {
        /** @var User $user */
        $user = Auth::user();

        $data = ['threads' => $this->notificationService->getMyNotifications($user->id)];

        $this->dumpDebugJson($data, 'notifications-index-data.json');

        return Inertia::render('notifications/index', $data);
    }
}
