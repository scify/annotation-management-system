<?php

declare(strict_types=1);

use App\Http\Controllers\AnnotationTaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\NotificationController;
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
    Route::post('projects/change-status', [ProjectController::class, 'changeStatus'])->name('projects.change-status');
    Route::post('sub-projects/change-status', [SubProjectController::class, 'changeStatus'])->name('sub-projects.change-status');
    Route::get('projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
    Route::delete('projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::delete('projects/{projectId}/subprojects/{subprojectId}', [SubProjectController::class, 'destroy'])->name('projects.subprojects.destroy');
    Route::delete('projects/{id}/annotators/{annotatorId}', [ProjectController::class, 'detachAnnotator'])->name('projects.annotators.detach');
    Route::delete('projects/{id}/managers/{managerId}', [ProjectController::class, 'removeManager'])->name('projects.managers.remove');
    Route::get('projects/{id}/add-annotators', [ProjectController::class, 'showAddAnnotators'])->name('projects.annotators.add');
    Route::post('projects/{id}/annotators', [ProjectController::class, 'attachAnnotators'])->name('projects.annotators.attach');
    Route::post('projects/{id}/propose-ownership', [ProjectController::class, 'proposeOwnership'])->name('projects.propose-ownership');
    Route::post('projects/{id}/accept-ownership', [ProjectController::class, 'acceptOwnership'])->name('projects.accept-ownership');
    Route::post('projects/{id}/reject-ownership', [ProjectController::class, 'rejectOwnership'])->name('projects.reject-ownership');
    Route::post('projects/{id}/cancel-ownership', [ProjectController::class, 'cancelOwnership'])->name('projects.cancel-ownership');
    Route::post('projects/{id}/request-to-leave', [ProjectController::class, 'requestToLeave'])->name('projects.request-to-leave');
    Route::post('projects/{id}/cancel-leave-request', [ProjectController::class, 'cancelRequestToLeave'])->name('projects.cancel-leave-request');
    Route::post('projects/{id}/reject-leave-request', [ProjectController::class, 'rejectRequestToLeave'])->name('projects.reject-leave-request');
    Route::delete('projects/{id}/leave-requests/{managerId}', [ProjectController::class, 'acceptRequestToLeave'])->name('projects.accept-leave-request');
    Route::get('projects/{id}/export', [ProjectController::class, 'export'])->name('projects.export');
    Route::get('projects/{id}/subprojects/create', [SubProjectController::class, 'create'])->name('projects.subprojects.create');
    Route::post('projects/{id}/subprojects', [SubProjectController::class, 'store'])->name('projects.subprojects.store');
    Route::get('projects/{projectId}/subprojects/{subprojectId}/edit', [SubProjectController::class, 'edit'])->name('projects.subprojects.edit');
    Route::put('projects/{projectId}/subprojects/{subprojectId}', [SubProjectController::class, 'update'])->name('projects.subprojects.update');
    Route::delete('projects/{projectId}/subprojects/{subprojectId}/annotators/{annotatorId}', [SubProjectController::class, 'detachAnnotator'])->name('projects.subprojects.annotators.detach');
    Route::get('projects/{projectId}/subprojects/{subprojectId}/add-annotators', [SubProjectController::class, 'showAddAnnotators'])->name('projects.subprojects.annotators.add');
    Route::post('projects/{projectId}/subprojects/{subprojectId}/annotators', [SubProjectController::class, 'attachAnnotators'])->name('projects.subprojects.annotators.attach');

    Route::get('users/{user}/connect-annotators', [UserController::class, 'showConnectAnnotators'])->name('users.annotators.add');
    Route::post('users/{user}/connect-annotators', [UserController::class, 'connectAnnotators'])->name('users.annotators.connect');

    Route::resource('users', UserController::class)->except('show');

    Route::put('/users/{user}/restore', UserRestoreController::class)->name('users.restore')->withTrashed();

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');

    Route::get('subprojects/{subProject}/annotation-task', [AnnotationTaskController::class, 'show'])->name('annotation-tasks.show');
});

// NOSONAR - this comes from Laravel
require __DIR__ . '/settings.php';
// NOSONAR - this comes from Laravel
require __DIR__ . '/auth.php';
