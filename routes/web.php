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
use Illuminate\Support\Facades\Route;

Route::put('locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/', fn () => redirect()->to(route('dashboard'))->withHeaders([
    'Cache-Control' => 'no-cache, no-store, must-revalidate',
    'Pragma' => 'no-cache',
    'Expires' => '0',
]))->name('home');

Route::middleware(['auth'])->group(function (): void {
    // ----- Dashboard -----
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ----- Monitor -----
    Route::controller(MonitorController::class)->prefix('monitor')->name('monitor.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('annotator-progress', 'annotatorProgress')->name('annotator-progress');
        Route::get('annotator-history', 'annotatorHistory')->name('annotator-history');
    });

    // ----- Projects -----
    Route::resource('projects', ProjectController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy'])
        ->parameters(['projects' => 'id']);

    Route::controller(ProjectController::class)->prefix('projects')->name('projects.')->group(function (): void {
        Route::post('toggle-can-flag', 'toggleCanFlagOfAnnotator')->name('toggle-can-flag');
        Route::post('change-status', 'changeStatus')->name('change-status');
        Route::get('{id}/export', 'export')->name('export');
        Route::get('{id}/add-annotators', 'showAddAnnotators')->name('annotators.add');
        Route::post('{id}/annotators', 'attachAnnotators')->name('annotators.attach');
        Route::delete('{id}/annotators/{annotatorId}', 'detachAnnotator')->name('annotators.detach');
        Route::delete('{id}/managers/{managerId}', 'removeManager')->name('managers.remove');
        Route::post('{id}/propose-ownership', 'proposeOwnership')->name('propose-ownership');
        Route::post('{id}/accept-ownership', 'acceptOwnership')->name('accept-ownership');
        Route::post('{id}/reject-ownership', 'rejectOwnership')->name('reject-ownership');
        Route::post('{id}/cancel-ownership', 'cancelOwnership')->name('cancel-ownership');
        Route::post('{id}/request-to-leave', 'requestToLeave')->name('request-to-leave');
        Route::post('{id}/cancel-leave-request', 'cancelRequestToLeave')->name('cancel-leave-request');
        Route::post('{id}/reject-leave-request', 'rejectRequestToLeave')->name('reject-leave-request');
        Route::delete('{id}/leave-requests/{managerId}', 'acceptRequestToLeave')->name('accept-leave-request');
    });

    // ----- Sub-projects -----
    Route::controller(SubProjectController::class)->group(function (): void {
        Route::post('sub-projects/change-status', 'changeStatus')->name('sub-projects.change-status');

        Route::prefix('projects')->name('projects.subprojects.')->group(function (): void {
            Route::get('{id}/subprojects/create', 'create')->name('create');
            Route::post('{id}/subprojects', 'store')->name('store');
            Route::get('{projectId}/subprojects/{subprojectId}/edit', 'edit')->name('edit');
            Route::put('{projectId}/subprojects/{subprojectId}', 'update')->name('update');
            Route::delete('{projectId}/subprojects/{subprojectId}', 'destroy')->name('destroy');
            Route::get('{projectId}/subprojects/{subprojectId}/add-annotators', 'showAddAnnotators')->name('annotators.add');
            Route::post('{projectId}/subprojects/{subprojectId}/annotators', 'attachAnnotators')->name('annotators.attach');
            Route::delete('{projectId}/subprojects/{subprojectId}/annotators/{annotatorId}', 'detachAnnotator')->name('annotators.detach');
        });
    });

    // ----- Users -----
    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function (): void {
        Route::get('{user}/connect-annotators', 'showConnectAnnotators')->name('annotators.add');
        Route::post('{user}/connect-annotators', 'connectAnnotators')->name('annotators.connect');
    });
    Route::resource('users', UserController::class)->except('show');

    // ----- Notifications -----
    Route::controller(NotificationController::class)->prefix('notifications')->name('notifications.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('read-all', 'markAllAsRead')->name('read-all');
        Route::post('{notificationThreadId}/approve', 'approve')->name('approve');
        Route::post('{notificationThreadId}/reject', 'reject')->name('reject');
        Route::post('{notificationThreadId}/reply', 'reply')->name('reply');
        Route::post('{notificationThreadId}/read', 'markAsRead')->name('read');
        Route::post('{notificationThreadId}/unread', 'markAsUnread')->name('unread');
        Route::post('send', 'sendMessage')->name('send');
        Route::post('send-announcement', 'sendAnnouncement')->name('send-announcement');
    });

    // ----- Annotation tasks -----
    Route::get('subprojects/{subProject}/annotation-task', [AnnotationTaskController::class, 'show'])->name('annotation-tasks.show');
});

// NOSONAR - this comes from Laravel
require __DIR__ . '/settings.php';
// NOSONAR - this comes from Laravel
require __DIR__ . '/auth.php';
