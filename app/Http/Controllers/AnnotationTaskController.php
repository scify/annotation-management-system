<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnnotationTaskController extends Controller {
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

        return Inertia::render('annotation-task/index', [
            'subProjectId' => $subProject,
            'mode' => $mode,
        ]);
    }
}
