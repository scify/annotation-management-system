<?php

declare(strict_types=1);

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

$SETTINGS_PROFILE_ROUTE = 'settings/profile';
$SETTINGS_PASSWORD_ROUTE = 'settings/password';

Route::middleware('auth')->group(function () use ($SETTINGS_PROFILE_ROUTE, $SETTINGS_PASSWORD_ROUTE): void {
    Route::redirect('settings', $SETTINGS_PROFILE_ROUTE);

    Route::get($SETTINGS_PROFILE_ROUTE, [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch($SETTINGS_PROFILE_ROUTE, [ProfileController::class, 'update'])->name('profile.update');
    Route::delete($SETTINGS_PROFILE_ROUTE, [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get($SETTINGS_PASSWORD_ROUTE, [PasswordController::class, 'edit'])->name('password.edit');
    Route::put($SETTINGS_PASSWORD_ROUTE, [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', fn () => Inertia::render('settings/appearance'))->name('appearance');
});
