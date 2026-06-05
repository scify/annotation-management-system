<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\PresentableError;
use App\Http\Requests\SubProject\DetachAnnotatorFromSubProjectRequest;
use App\Http\Requests\SubProject\SubProjectStoreRequest;
use App\Models\Project;
use App\Services\SubProject\SubProjectService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SubProjectController extends Controller {
    use AuthorizesRequests;

    public function __construct(private readonly SubProjectService $subProjectService) {}

    public function create(int $id): Response {
        $this->authorize('viewAny', Project::class);

        $data_for_create_sub_project = $this->subProjectService->getDataForCreateSubProject($id);

        $json = json_encode($data_for_create_sub_project, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            Storage::disk('local')->put('subproject-create-data.json', $json);
        }

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

        $json = json_encode($validated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            Storage::disk('local')->put('subproject-store-data.json', $json);
        }

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

    public function edit(int $projectId, int $subprojectId): Response {
        $this->authorize('viewAny', Project::class);

        $data_for_edit_subproject = $this->subProjectService->getDataForEditSubProject($projectId, $subprojectId);

        $json = json_encode($data_for_edit_subproject, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            Storage::disk('local')->put('subproject-edit-data.json', $json);
        }

        return Inertia::render('sub-projects/edit');
    }
}
