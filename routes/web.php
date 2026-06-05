<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubProjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRestoreController;
use Illuminate\Support\Facades\Route;

Route::put('locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/', fn () => redirect()->to(route('dashboard'))->withHeaders([
    'Cache-Control' => 'no-cache, no-store, must-revalidate',
    'Pragma' => 'no-cache',
    'Expires' => '0',
]))->name('home');

Route::middleware(['auth'])->group(function (): void {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('monitor', [MonitorController::class, 'index'])->name('monitor.index');
    Route::get('monitor/annotator-progress', [MonitorController::class, 'annotatorProgress'])->name('monitor.annotator-progress');
    Route::get('monitor/annotator-history', [MonitorController::class, 'annotatorHistory'])->name('monitor.annotator-history');

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::post('projects/toggle-can-flag', [ProjectController::class, 'toggleCanFlagOfAnnotator'])->name('projects.toggle-can-flag');
    Route::get('projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
    Route::delete('projects/{id}/annotators/{annotatorId}', [ProjectController::class, 'detachAnnotator'])->name('projects.annotators.detach');
    Route::get('projects/{id}/add-annotators', [ProjectController::class, 'showAddAnnotators'])->name('projects.annotators.add');
    Route::post('projects/{id}/annotators', [ProjectController::class, 'attachAnnotators'])->name('projects.annotators.attach');
    Route::get('projects/{id}/export', [ProjectController::class, 'export'])->name('projects.export');
    Route::get('projects/{id}/subprojects/create', [SubProjectController::class, 'create'])->name('projects.subprojects.create');
    Route::post('projects/{id}/subprojects', [SubProjectController::class, 'store'])->name('projects.subprojects.store');
    Route::get('projects/{projectId}/subprojects/{subprojectId}/edit', [SubProjectController::class, 'edit'])->name('projects.subprojects.edit');
    Route::delete('projects/{projectId}/subprojects/{subprojectId}/annotators/{annotatorId}', [SubProjectController::class, 'detachAnnotator'])->name('projects.subprojects.annotators.detach');

    Route::resource('users', UserController::class)->except('show');

    Route::put('/users/{user}/restore', UserRestoreController::class)->name('users.restore')->withTrashed();
});

// NOSONAR - this comes from Laravel
require __DIR__ . '/settings.php';
// NOSONAR - this comes from Laravel
require __DIR__ . '/auth.php';
