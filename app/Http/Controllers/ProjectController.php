<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller {
    public function index(): Response {
        return Inertia::render('projects/index');
    }

    public function create(): Response {
        return Inertia::render('projects/create');
    }

    public function show(int $id): Response {
        return Inertia::render('projects/show');
    }
}
