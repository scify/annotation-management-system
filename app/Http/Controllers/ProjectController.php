<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\PresentableError;
use App\Http\Requests\Project\DetachAnnotatorFromProjectRequest;
use App\Http\Requests\Project\ProjectExportRequest;
use App\Http\Requests\Project\ProjectStoreRequest;
use App\Http\Requests\Project\ToggleCanFlagRequest;
use App\Models\Project;
use App\Models\User;
use App\Services\Annotation\AnnotatorService;
use App\Services\Project\ProjectService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProjectController extends Controller {
    use AuthorizesRequests;

    public function __construct(
        private readonly ProjectService $projectService,
        private readonly AnnotatorService $annotatorService,
    ) {}

    public function index(): Response {
        $this->authorize('viewAny', Project::class);

        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data_for_projects = $this->projectService->getDataForProjectsPage($user);

        $this->dumpDebugJson($data_for_projects, 'project-index-data.json');

        return Inertia::render('projects/index', $data_for_projects);
    }

    public function create(): Response {
        $this->authorize('create', Project::class);

        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data_for_create_project = $this->projectService->getDataForCreateProject($user);

        $this->dumpDebugJson($data_for_create_project, 'project-create-data.json');

        return Inertia::render('projects/create', $data_for_create_project);
    }

    /**
     * @throws Throwable
     */
    public function store(ProjectStoreRequest $request): RedirectResponse {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $project = $this->projectService->storeProject($user, $request->validated());

        return to_route('projects.create')
            ->with('created_project_name', $project->name);
    }

    public function export(ProjectExportRequest $request, int $id): StreamedResponse {
        return response()->streamDownload(
            static function (): void { echo json_encode((object) []); },
            'export.json',
            ['Content-Type' => 'application/json'],
        );
    }

    public function toggleCanFlagOfAnnotator(ToggleCanFlagRequest $request): RedirectResponse {
        $this->annotatorService->toggleCanFlag(
            $request->integer('annotator_id'),
            $request->integer('project_id'),
        );

        return to_route('projects.show', $request->integer('project_id'))
            ->with('success', __('projects.messages.can_flag_toggled'));
    }

    public function detachAnnotator(DetachAnnotatorFromProjectRequest $request, int $id): RedirectResponse {
        try {
            $this->projectService->detachAnnotator($id, $request->integer('annotator_id'));
        } catch (PresentableError $presentableError) {
            return to_route('projects.show', $id)->with('error', $presentableError->getUserMessage());
        }

        return to_route('projects.show', $id)->with('success', __('projects.messages.annotator_detached'));
    }

    public function showAddAnnotators(int $id): Response {
        $this->authorize('viewAny', Project::class);

        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $project = Project::query()->findOrFail($id);

        $data_for_add_annotators = [
            'project_id' => $project->id,
            'project_name' => $project->name,
            ...$this->projectService->getDataForAddAnnotators($id, $user),
        ];

        $this->dumpDebugJson($data_for_add_annotators, 'project-add-annotators-data.json');

        return Inertia::render('projects/add-annotators', $data_for_add_annotators);
    }

    public function attachAnnotators(Request $request, int $id): RedirectResponse {
        // TODO: wire to ProjectService
        return to_route('projects.show', $id)
            ->with('success', __('projects.messages.annotators_attached'));
    }

    public function show(int $id): Response {
        $this->authorize('viewAny', Project::class);

        $data_for_show_project = $this->projectService->getDataForShowProject($id);

        $this->dumpDebugJson($data_for_show_project, 'project-show-data.json');

        return Inertia::render('projects/show', $data_for_show_project);
    }
}
