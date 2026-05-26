<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\Project\SubProjectService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SubProjectController extends Controller {
    use AuthorizesRequests;

    public function __construct(private readonly SubProjectService $subProjectService) {}

    public function create(int $id): Response {
        return Inertia::render('sub-projects/create');
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
