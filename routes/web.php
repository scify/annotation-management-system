<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
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

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::get('projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('projects/{id}/subprojects/create', [SubProjectController::class, 'create'])->name('projects.subprojects.create');
    Route::get('projects/{projectId}/subprojects/{subprojectId}/edit', [SubProjectController::class, 'edit'])->name('projects.subprojects.edit');

    Route::resource('users', UserController::class);

    Route::put('/users/{user}/restore', UserRestoreController::class)->name('users.restore')->withTrashed();
});

// NOSONAR - this comes from Laravel
require __DIR__ . '/settings.php';
// NOSONAR - this comes from Laravel
require __DIR__ . '/auth.php';
