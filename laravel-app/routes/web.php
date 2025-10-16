<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\IndexController as AdminIndexController;

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// Admin routes (migrated from ThinkPHP admin module)
Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
    Route::get('/', [AdminIndexController::class, 'index'])->name('index');
    Route::get('/dashboard', [AdminIndexController::class, 'index'])->name('dashboard');
    Route::post('/wipe-cache', [AdminIndexController::class, 'wipeCache'])->name('wipe-cache');
    Route::get('/profile', [AdminIndexController::class, 'profile'])->name('profile');
    Route::post('/profile', [AdminIndexController::class, 'updateProfile'])->name('update-profile');
    Route::get('/check-update', [AdminIndexController::class, 'checkUpdate'])->name('check-update');
});
