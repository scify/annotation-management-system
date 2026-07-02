<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Annotation\ExitAnnotationRequest;
use App\Http\Requests\Annotation\FlagAnnotationRequest;
use App\Http\Requests\Annotation\NextAnnotationRequest;
use App\Http\Requests\Annotation\PreviousAnnotationRequest;
use App\Http\Requests\Annotation\SendToManagerAnnotationRequest;
use App\Http\Requests\Annotation\ShowAnnotationRequest;
use App\Http\Requests\Annotation\SubmitAnnotationRequest;
use App\Http\Requests\Annotation\SubmitPendingAnnotationRequest;
use App\Services\Annotation\AnnotationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AnnotationController extends Controller {
    public function __construct(
        private readonly AnnotationService $annotationService,
    ) {}

    public function sendToManager(SendToManagerAnnotationRequest $request, int $subProject): JsonResponse {
        $user = $this->authUser();

        $this->annotationService->sendToManager($request, $subProject, $user->id);

        return $this->jsonSuccess(__('annotation.send_to_manager.success'));
    }

    public function flagInstance(FlagAnnotationRequest $request, int $subProject): RedirectResponse {
        $user = $this->authUser();

        $this->annotationService->flagInstance($request, $subProject, $user->id);

        return to_route('annotation.show', ['subProject' => $subProject, 'active_filter' => $request->activeFilter()->value])
            ->with('success', __('annotation.flag_success'));
    }

    public function submitAnnotation(SubmitAnnotationRequest $request, int $subProject): RedirectResponse {
        $user = $this->authUser();

        $this->annotationService->submitAnnotation($request, $subProject, $user->id);

        return to_route('annotation.show', ['subProject' => $subProject, 'active_filter' => $request->activeFilter()->value, 'annotation_session_id' => $request->integer('annotation_session_id')])
            ->with('success', __('annotation.submit_success'));
    }

    public function previous(PreviousAnnotationRequest $request, int $subProject): RedirectResponse {
        $this->authUser();

        return to_route('annotation.show', ['subProject' => $subProject, 'active_filter' => $request->activeFilter()->value]);
    }

    public function next(NextAnnotationRequest $request, int $subProject): RedirectResponse {
        $this->authUser();

        return to_route('annotation.show', ['subProject' => $subProject, 'active_filter' => $request->activeFilter()->value]);
    }

    public function submitPending(SubmitPendingAnnotationRequest $request, int $subProject): RedirectResponse {
        $user = $this->authUser();

        $this->annotationService->submitPending($subProject, $user->id);

        return to_route('annotation.show', ['subProject' => $subProject])
            ->with('success', __('annotation.submit_success'));
    }

    public function exitAnnotation(ExitAnnotationRequest $request, int $subProject): RedirectResponse {
        $this->authUser();

        $this->annotationService->stopSession($request->annotationSessionId());

        return to_route('dashboard')
            ->with('success', __('annotation.exit_annotation_success'));
    }

    public function show(ShowAnnotationRequest $request, int $subProject): Response {
        $user = $this->authUser();

        $data = $this->annotationService->getAnnotationViewData($subProject, $user->id, $request->activeFilter(), $request->annotationSessionId());

        $this->dumpDebugJson($data, 'annotation-show-data.json');

        return Inertia::render('annotation/show', $data);
    }
}
