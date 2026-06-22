<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller {
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function index(): Response|RedirectResponse {
        /** @var User $user */
        $user = Auth::user();
        $isAnnotator = $user->hasRole(RolesEnum::ANNOTATOR->value);

        $data_for_dashboard = $isAnnotator
            ? $this->dashboardService->getDataForAnnotatorDashboard($user)
            : $this->dashboardService->getDataForDashboard($user);

        $this->dumpDebugJson($data_for_dashboard, $isAnnotator ? 'dashboard-annotator-data.json' : 'dashboard-data.json');

        return Inertia::render(
            $isAnnotator ? 'dashboard-annotator' : 'dashboard',
            $data_for_dashboard,
        );
    }
}
