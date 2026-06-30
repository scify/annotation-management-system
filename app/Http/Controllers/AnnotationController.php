<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Annotation\FlagAnnotationRequest;
use App\Http\Requests\Annotation\SendToManagerAnnotationRequest;
use App\Http\Requests\Annotation\ShowAnnotationRequest;
use App\Http\Requests\Annotation\SubmitAnnotationRequest;
use App\Http\Requests\Annotation\SubmitPendingAnnotationRequest;
use App\Models\User;
use App\Services\Annotation\AnnotationService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AnnotationController extends Controller {
    public function __construct(
        private readonly AnnotationService $annotationService,
    ) {}

    public function sendToManager(SendToManagerAnnotationRequest $request, int $subProject): Response {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data = $this->annotationService->sendToManager($request, $subProject, $user->id);

        $this->dumpDebugJson($data, 'annotation-show-data.json');

        return Inertia::render('annotation/show', $data);
    }

    public function flagInstance(FlagAnnotationRequest $request, int $subProject): Response {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data = $this->annotationService->flagInstance($request, $subProject, $user->id);

        $this->dumpDebugJson($data, 'annotation-show-data.json');

        return Inertia::render('annotation/show', $data);
    }

    public function submitAnnotation(SubmitAnnotationRequest $request, int $subProject): Response {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data = $this->annotationService->submitAnnotation($request, $subProject, $user->id);

        $this->dumpDebugJson($data, 'annotation-show-data.json');

        return Inertia::render('annotation/show', $data);
    }

    public function submitPending(SubmitPendingAnnotationRequest $request, int $subProject): Response {
        $mode = $request->string('mode')->toString();

        if (! in_array($mode, ['strict', 'flexible'], true)) {
            $mode = 'strict';
        }

        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $this->annotationService->submitPending($subProject, $user->id);

        $data = $this->annotationService->getInitialViewData($subProject, $mode, $user->id);

        $this->dumpDebugJson($data, 'annotation-show-data.json');

        return Inertia::render('annotation/show', $data);
    }

    /**
     * Render the annotation tool for a subproject.
     *
     * The annotation payload is mocked on the frontend for now, so this only
     * passes through the subproject id and the requested browsing mode. The
     * mode is validated against the supported set and falls back to strict.
     */
    public function show(ShowAnnotationRequest $request, int $subProject): Response {
        $mode = $request->string('mode')->toString();

        if (! in_array($mode, ['strict', 'flexible'], true)) {
            $mode = 'strict';
        }

        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data = $this->annotationService->getInitialViewData($subProject, $mode, $user->id);

        $this->dumpDebugJson($data, 'annotation-show-data.json');

        return Inertia::render('annotation/show', $data);
    }
}
