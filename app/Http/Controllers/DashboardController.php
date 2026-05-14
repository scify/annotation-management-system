<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller {
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function index(): Response|RedirectResponse {
        /** @var User $user */
        $user = Auth::user();
        if ($user->hasRole(RolesEnum::ANNOTATOR->value)) {
            return Inertia::render('dashboard-simple');
        }

        $data_for_dashboard = $this->dashboardService->getDataForDashboard($user);

        $json = json_encode($data_for_dashboard, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            Storage::disk('local')->put('dashboard-data.json', $json);
        }

        return Inertia::render('dashboard', $data_for_dashboard);

    }
}
