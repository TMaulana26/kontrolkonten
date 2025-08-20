<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return redirect()->intended(route('login'));
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::group(['prefix' => 'menu'], function () {
        Route::patch('{menu}/toggle-status', [MenuController::class, 'toggleStatus'])->name('menu.toggleStatus');
        Route::post('{id}/restore', [MenuController::class, 'restore'])->name('menu.restore');
        Route::delete('{id}/force-delete', [MenuController::class, 'forceDelete'])->name('menu.forceDelete');
    });
    Route::resource('menu', MenuController::class)->except(['create', 'show', 'edit']);
    Route::resource('user', UserController::class)->except(['create', 'show', 'edit']);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
