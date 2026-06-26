<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Annotation\AnnotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AnnotationController extends Controller {
    public function __construct(
        private readonly AnnotationService $annotationService,
    ) {}

    /**
     * Render the annotation tool for a subproject.
     *
     * The annotation payload is mocked on the frontend for now, so this only
     * passes through the subproject id and the requested browsing mode. The
     * mode is validated against the supported set and falls back to strict.
     */
    public function show(Request $request, int $subProject): Response {
        $mode = $request->string('mode')->toString();

        if (! in_array($mode, ['strict', 'flexible'], true)) {
            $mode = 'strict';
        }

        $annotationAssignmentId = $request->integer('annotation_assignment_id');
        $nextAnnotationId = $request->integer('next_annotation_id') ?: null;

        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        if ($nextAnnotationId === null) {
            $data = $this->annotationService->getInitialViewData($subProject, $mode, $user->id);
        } else {
            $annotationSessionId = $this->annotationService->startSession($annotationAssignmentId, $nextAnnotationId);
            $data = $this->annotationService->getDataForShowAnnotation($subProject, $mode, $user->id, $annotationSessionId, $nextAnnotationId);
        }

        $this->dumpDebugJson($data, 'annotation-show-data.json');

        return Inertia::render('annotation-task/index', $data);
    }
}
