<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class SubProjectController extends Controller {
    public function create(int $id): Response {
        return Inertia::render('sub-projects/create');
    }
}
