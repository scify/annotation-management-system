<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\PresentableError;
use App\Http\Requests\SubProject\DetachAnnotatorFromSubProjectRequest;
use App\Http\Requests\SubProject\SubProjectStoreRequest;
use App\Models\Project;
use App\Models\SubProject;
use App\Services\SubProject\SubProjectService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SubProjectController extends Controller {
    use AuthorizesRequests;

    public function __construct(private readonly SubProjectService $subProjectService) {}

    public function create(int $id): Response {
        $this->authorize('viewAny', Project::class);

        $data_for_create_sub_project = $this->subProjectService->getDataForCreateSubProject($id);

        $this->dumpDebugJson($data_for_create_sub_project, 'subproject-create-data.json');

        return Inertia::render('sub-projects/create', [
            ...$data_for_create_sub_project,
            'created_subproject_name' => session()->pull('created_subproject_name'),
        ]);
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

        $project = Project::query()->findOrFail($projectId);
        $subproject = SubProject::query()->findOrFail($subprojectId);

        $data = [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'subproject_id' => $subproject->id,
            'subproject_name' => $subproject->name,
            ...$this->subProjectService->getDataForAddAnnotators($projectId, $subprojectId),
        ];

        $this->dumpDebugJson($data, 'subproject-add-annotators-data.json');

        return Inertia::render('sub-projects/add-annotators', $data);
    }

    public function attachAnnotators(Request $request, int $projectId, int $subprojectId): RedirectResponse {
        // TODO: wire to SubProjectService
        return to_route('projects.subprojects.edit', [$projectId, $subprojectId])
            ->with('success', __('projects.messages.annotators_attached'));
    }

    public function edit(int $projectId, int $subprojectId): Response {
        $this->authorize('viewAny', Project::class);

        $data_for_edit_subproject = $this->subProjectService->getDataForEditSubProject($projectId, $subprojectId);

        $this->dumpDebugJson($data_for_edit_subproject, 'subproject-edit-data.json');

        return Inertia::render('sub-projects/edit', [
            ...$data_for_edit_subproject,
            // TODO move project_name to getDataForEditSubProject call
            'project_name' => Project::query()->select('id', 'name')->findOrFail($projectId)->name,
        ]);
    }
}
