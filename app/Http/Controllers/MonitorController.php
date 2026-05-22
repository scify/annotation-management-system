<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Monitor\MonitorActiveWorkTabService;
use App\Services\Monitor\MonitorHistoryTabService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MonitorController extends Controller {
    public function __construct(
        private readonly MonitorActiveWorkTabService $activeWorkTabService,
        private readonly MonitorHistoryTabService $historyTabService,
    ) {}

    public function index(): RedirectResponse {
        return to_route('monitor.active-work');
    }

    public function activeWork(): Response {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data = $this->activeWorkTabService->getData($user);

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            Storage::disk('local')->put('monitor-active-work-data.json', $json);
        }

        return Inertia::render('monitor/index', ['active_work_tab_data' => $data]);
    }

    public function history(): Response {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data = $this->historyTabService->getData($user);

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            Storage::disk('local')->put('monitor-history-data.json', $json);
        }

        return Inertia::render('monitor/index', ['history_tab_data' => $data]);
    }
}
