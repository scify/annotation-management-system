<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Services\Project\ProjectService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller {
    use AuthorizesRequests;

    public function __construct(private readonly ProjectService $projectService) {}

    public function index(): Response {
        $this->authorize('viewAny', Project::class);

        return Inertia::render('projects/index');
    }

    public function create(): Response {
        $this->authorize('create', Project::class);

        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data_for_create_project = $this->projectService->getDataForCreateProject($user);

        //        Storage::disk('local')->put(
        //            'project-create-data.json',
        //            json_encode(
        //                $data_for_create_project,
        //                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        //            )
        //        );

        return Inertia::render('projects/create', $data_for_create_project);
    }

    public function show(int $id): Response {
        $this->authorize('viewAny', Project::class);

        return Inertia::render('projects/show');
    }
}
