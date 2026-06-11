<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller {
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(): JsonResponse {
        /** @var User $user */
        $user = Auth::user();

        $data = ['threads' => $this->notificationService->getMyNotifications($user->id)];

        $this->dumpDebugJson($data, 'notifications-index-data.json');

        return response()->json($data);
    }
}
