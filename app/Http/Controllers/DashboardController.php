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
        if ($user->hasRole(RolesEnum::ANNOTATOR->value)) {
            return Inertia::render('dashboard-simple');
        }

        $projects = $user->hasRole(RolesEnum::ADMIN->value)
            ? $this->dashboardService->getAllInProgressProjects()
            : $this->dashboardService->getMyInProgressProjects($user->id);

        return Inertia::render('dashboard', ['projects' => $projects]);

    }
}
