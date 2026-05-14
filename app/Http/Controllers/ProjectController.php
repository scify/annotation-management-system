<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Project\ProjectStoreRequest;
use App\Models\Project;
use App\Models\User;
use App\Services\Project\ProjectService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ProjectController extends Controller {
    use AuthorizesRequests;

    public function __construct(private readonly ProjectService $projectService) {}

    public function index(): Response {
        $this->authorize('viewAny', Project::class);

        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data_for_projects = $this->projectService->getDataForProjects($user);

        $json = json_encode($data_for_projects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            Storage::disk('local')->put('project-index-data.json', $json);
        }

        return Inertia::render('projects/index', $data_for_projects);
    }

    public function create(): Response {
        $this->authorize('create', Project::class);

        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data_for_create_project = $this->projectService->getDataForCreateProject($user);

        $json = json_encode($data_for_create_project, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            Storage::disk('local')->put('project-create-data.json', $json);
        }

        return Inertia::render('projects/create', $data_for_create_project);
    }

    /**
     * @throws Throwable
     */
    public function store(ProjectStoreRequest $request): RedirectResponse {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $this->projectService->createProject($user, $request->validated());

        return to_route('projects.index')
            ->with('success', __('projects.messages.created'));
    }

    public function show(int $id): Response {
        $this->authorize('viewAny', Project::class);

        return Inertia::render('projects/show');
    }
}
