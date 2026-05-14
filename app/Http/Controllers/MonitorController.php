<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Monitor\MonitorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MonitorController extends Controller {
    public function __construct(
        private readonly MonitorService $monitorService,
    ) {}

    public function index(): Response {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data = $this->monitorService->getDataForMonitor($user);

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            Storage::disk('local')->put('monitor-index-data.json', $json);
        }

        return Inertia::render('monitor/index', $data);
    }
}
