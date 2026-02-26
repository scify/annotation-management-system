<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRestoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->to(route('dashboard'))->withHeaders([
    'Cache-Control' => 'no-cache, no-store, must-revalidate',
    'Pragma' => 'no-cache',
    'Expires' => '0',
]))->name('home');

Route::middleware(['auth'])->group(function (): void {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class);

    Route::put('/users/{user}/restore', UserRestoreController::class)->name('users.restore')->withTrashed();
});

// NOSONAR - this comes from Laravel
require __DIR__ . '/settings.php';
// NOSONAR - this comes from Laravel
require __DIR__ . '/auth.php';
