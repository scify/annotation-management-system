<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProjectStatusEnum;
use App\Exceptions\PresentableError;
use App\Http\Requests\Project\AttachAnnotatorsToProjectRequest;
use App\Http\Requests\Project\DetachAnnotatorFromProjectRequest;
use App\Http\Requests\Project\ProjectChangeStatusRequest;
use App\Http\Requests\Project\ProjectExportRequest;
use App\Http\Requests\Project\ProjectStoreRequest;
use App\Http\Requests\Project\ToggleCanFlagRequest;
use App\Models\Project;
use App\Models\User;
use App\Services\Annotation\AnnotatorService;
use App\Services\Project\ProjectReadService;
use App\Services\Project\ProjectWriteService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProjectController extends Controller {
    use AuthorizesRequests;

    public function __construct(
        private readonly ProjectWriteService $projectService,
        private readonly ProjectReadService $projectReadService,
        private readonly AnnotatorService $annotatorService,
    ) {}

    public function index(): Response {
        $this->authorize('viewAny', Project::class);

        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data = $this->projectReadService->getDataForProjectsPage($user);

        $this->dumpDebugJson($data, 'project-index-data.json');

        return Inertia::render('projects/index', $data);
    }

    public function create(): Response {
        $this->authorize('create', Project::class);

        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data = $this->projectReadService->getDataForCreateProject($user);

        $this->dumpDebugJson($data, 'project-create-data.json');

        return Inertia::render('projects/create', $data);
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
            Log::info('Detaching annotator from project', [
                'project_id' => $id,
                'annotator_id' => $request->integer('annotator_id'),
            ]);
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

        $data = $this->projectReadService->getDataForAddAnnotators($id, $user);

        $this->dumpDebugJson($data, 'project-add-annotators-data-' . $user->role . '.json');

        return Inertia::render('projects/add-annotators', $data);
    }

    public function attachAnnotators(AttachAnnotatorsToProjectRequest $request, int $id): RedirectResponse {
        /** @var array<int, int> $annotatorIds */
        $annotatorIds = $request->validated('annotator_ids');

        $this->projectService->attachAnnotators($id, $annotatorIds);

        return to_route('projects.show', $id)
            ->with('success', __('projects.messages.annotators_attached'));
    }

    public function changeStatus(ProjectChangeStatusRequest $request): RedirectResponse {
        $project = Project::query()->findOrFail($request->integer('project_id'));

        try {
            $this->projectService->changeStatus(
                $project,
                ProjectStatusEnum::from($request->string('status')->value()),
            );
        } catch (PresentableError $presentableError) {
            return to_route('projects.show', $project->id)->with('error', $presentableError->getUserMessage());
        }

        return to_route('projects.show', $project->id)
            ->with('success', __('projects.messages.status_changed'));
    }

    public function destroy(int $id): RedirectResponse {
        $project = Project::query()->findOrFail($id);
        $this->authorize('delete', $project);

        $this->projectService->deleteProject($project);

        return to_route('projects.index')
            ->with('success', __('projects.messages.deleted'));
    }

    public function show(int $id): Response {
        $this->authorize('viewAny', Project::class);

        $data = $this->projectReadService->getDataForShowProject($id);

        $this->dumpDebugJson($data, 'project-show-data-' . Auth::user()->role . '.json');

        return Inertia::render('projects/show', $data);
    }
}
