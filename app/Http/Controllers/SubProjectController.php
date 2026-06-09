<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProjectStatusEnum;
use App\Exceptions\PresentableError;
use App\Http\Requests\SubProject\AttachAnnotatorsToSubProjectRequest;
use App\Http\Requests\SubProject\DetachAnnotatorFromSubProjectRequest;
use App\Http\Requests\SubProject\SubProjectChangeStatusRequest;
use App\Http\Requests\SubProject\SubProjectStoreRequest;
use App\Http\Requests\SubProject\SubProjectUpdateRequest;
use App\Models\Project;
use App\Models\SubProject;
use App\Services\SubProject\SubProjectReadService;
use App\Services\SubProject\SubProjectWriteService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SubProjectController extends Controller {
    use AuthorizesRequests;

    public function __construct(
        private readonly SubProjectWriteService $subProjectService,
        private readonly SubProjectReadService $subProjectReadService,
    ) {}

    public function create(int $id): Response {
        $this->authorize('viewAny', Project::class);

        $data = $this->subProjectReadService->getDataForCreateSubProject($id);

        $this->dumpDebugJson($data, 'subproject-create-data.json');

        return Inertia::render('sub-projects/create', [
            ...$data,
            'created_subproject_name' => session()->pull('created_subproject_name'),
        ]);
    }

    public function changeStatus(SubProjectChangeStatusRequest $request): RedirectResponse {
        $subProject = SubProject::query()->with('project')->findOrFail($request->integer('sub_project_id'));

        try {
            $this->subProjectService->changeStatus(
                $subProject,
                ProjectStatusEnum::from($request->string('status')->value()),
            );
        } catch (PresentableError $presentableError) {
            return to_route('projects.subprojects.edit', [$subProject->project_id, $subProject->id])
                ->with('error', $presentableError->getUserMessage());
        }

        return to_route('projects.subprojects.edit', [$subProject->project_id, $subProject->id])
            ->with('success', __('sub-projects.messages.status_changed'));
    }

    /**
     * @throws Throwable
     */
    public function store(SubProjectStoreRequest $request, int $id): RedirectResponse {
        $validated = $request->validated();

        $this->dumpDebugJson($validated, 'subproject-store-data.json');

        $this->subProjectService->storeSubProject($id, $validated);

        return to_route('projects.subprojects.create', $id)
            ->with('created_subproject_name', $request->validated()['name']);
    }

    public function detachAnnotator(DetachAnnotatorFromSubProjectRequest $request, int $projectId, int $subprojectId): RedirectResponse {
        try {
            $this->subProjectService->detachAnnotator($subprojectId, $request->integer('annotator_id'));
        } catch (PresentableError $presentableError) {
            return to_route('projects.subprojects.edit', [$projectId, $subprojectId])->with('error', $presentableError->getUserMessage());
        }

        return to_route('projects.subprojects.edit', [$projectId, $subprojectId])->with('success', __('sub-projects.messages.annotator_detached'));
    }

    public function showAddAnnotators(int $projectId, int $subprojectId): Response {
        $this->authorize('viewAny', Project::class);

        $data = $this->subProjectReadService->getDataForAddAnnotators($projectId, $subprojectId);

        $this->dumpDebugJson($data, 'subproject-add-annotators-data-' . Auth::user()->role . '.json');

        return Inertia::render('sub-projects/add-annotators', $data);
    }

    public function attachAnnotators(AttachAnnotatorsToSubProjectRequest $request, int $projectId, int $subprojectId): RedirectResponse {
        /** @var array<int, int> $annotatorIds */
        $annotatorIds = $request->validated('annotator_ids');

        $this->subProjectService->attachAnnotators($subprojectId, $annotatorIds);

        return to_route('projects.subprojects.edit', [$projectId, $subprojectId])
            ->with('success', __('projects.messages.annotators_attached'));
    }

    public function destroy(int $projectId, int $subprojectId): RedirectResponse {
        $subProject = SubProject::query()->findOrFail($subprojectId);
        $this->authorize('deleteSubProject', $subProject);

        $this->subProjectService->deleteSubProject($subProject);

        return to_route('projects.show', $projectId)
            ->with('success', __('sub-projects.messages.deleted'));
    }

    public function update(SubProjectUpdateRequest $request, int $projectId, int $subprojectId): RedirectResponse {
        $this->authorize('viewAny', Project::class);

        $subProject = SubProject::query()->findOrFail($subprojectId);

        $this->subProjectService->updateSubProject($subProject, $request->validated());

        return to_route('projects.subprojects.edit', [$projectId, $subprojectId])
            ->with('success', __('sub-projects.messages.updated'));
    }

    public function edit(int $projectId, int $subprojectId): Response {
        $this->authorize('viewAny', Project::class);

        $data = $this->subProjectReadService->getDataForEditSubProject($projectId, $subprojectId);

        $this->dumpDebugJson($data, 'subproject-edit-data.json');

        return Inertia::render('sub-projects/edit', $data);
    }
}
