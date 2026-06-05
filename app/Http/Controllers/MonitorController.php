<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Monitor\MonitorAnnotatorHistoryTabService;
use App\Services\Monitor\MonitorAnnotatorProgressTabService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MonitorController extends Controller {
    public function __construct(
        private readonly MonitorAnnotatorProgressTabService $annotatorProgressTabService,
        private readonly MonitorAnnotatorHistoryTabService $annotatorHistoryTabService,
    ) {}

    public function index(): RedirectResponse {
        return to_route('monitor.annotator-progress');
    }

    public function annotatorProgress(): Response {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data = $this->annotatorProgressTabService->getData($user);

        $this->dumpDebugJson($data, 'monitor-active-work-data.json');

        return Inertia::render('monitor/index', ['annotator_progress_tab_data' => $data]);
    }

    public function annotatorHistory(): Response {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data = $this->annotatorHistoryTabService->getData($user);

        $this->dumpDebugJson($data, 'monitor-history-data.json');

        return Inertia::render('monitor/index', ['annotator_history_tab_data' => $data]);
    }
}
