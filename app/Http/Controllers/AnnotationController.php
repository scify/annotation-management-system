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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AnnotationController extends Controller {
    public function __construct(
        private readonly AnnotationService $annotationService,
    ) {}

    public function sendToManager(SendToManagerAnnotationRequest $request, int $subProject): JsonResponse {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $this->annotationService->sendToManager($request, $subProject, $user->id);

        return $this->jsonSuccess(__('annotation.send_to_manager.success'));
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
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $this->annotationService->submitPending($subProject, $user->id);

        $data = $this->annotationService->getInitialViewData($subProject, $user->id);

        $this->dumpDebugJson($data, 'annotation-show-data.json');

        return Inertia::render('annotation/show', $data);
    }

    public function show(ShowAnnotationRequest $request, int $subProject): Response {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $data = $this->annotationService->getInitialViewData($subProject, $user->id);

        $this->dumpDebugJson($data, 'annotation-show-data.json');

        return Inertia::render('annotation/show', $data);
    }
}
