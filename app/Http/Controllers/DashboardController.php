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

        $data_for_dashboard = [];
        if ($user->hasRole(RolesEnum::ADMIN->value)) {
            $data_for_dashboard['platform_stats'] = $this->dashboardService->getPlatformStats();
            $data_for_dashboard['all_projects'] = $this->dashboardService->getAllInProgressProjects();
            $data_for_dashboard['all_annotators'] = $this->dashboardService->getAllAnnotators();
            $data_for_dashboard['my_projects'] = $this->dashboardService->getMyInProgressProjects($user->id, $data_for_dashboard['all_projects']);
            $data_for_dashboard['my_annotators'] = $this->dashboardService->getMyAnnotators($data_for_dashboard['my_projects'], $data_for_dashboard['all_annotators']);
        } else {
            $data_for_dashboard['my_projects'] = $this->dashboardService->getMyInProgressProjects($user->id);
            $data_for_dashboard['my_annotators'] = $this->dashboardService->getMyAnnotators($data_for_dashboard['my_projects']);
        }

        Storage::disk('local')->put(
            'dashboard-data.json',
            json_encode(
                $data_for_dashboard,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )
        );

        return Inertia::render('dashboard', $data_for_dashboard);

    }
}
