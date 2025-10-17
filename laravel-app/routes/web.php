<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\IndexController as AdminIndexController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Admin\ConfigController as AdminConfigController;
use App\Http\Controllers\Auth\LoginController;

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin routes (migrated from ThinkPHP admin module)
Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminIndexController::class, 'index'])->name('index');
    Route::get('/dashboard', [AdminIndexController::class, 'index'])->name('dashboard');
    
    // System
    Route::post('/wipe-cache', [AdminIndexController::class, 'wipeCache'])->name('wipe-cache');
    Route::get('/check-update', [AdminIndexController::class, 'checkUpdate'])->name('check-update');
    
    // Profile
    Route::get('/profile', [AdminIndexController::class, 'profile'])->name('profile');
    Route::post('/profile', [AdminIndexController::class, 'updateProfile'])->name('update-profile');
    
    // Menu Management
    Route::prefix('menu')->name('menu.')->group(function () {
        Route::get('/{group?}', [AdminMenuController::class, 'index'])->name('index');
        Route::get('/create/{module?}/{pid?}', [AdminMenuController::class, 'create'])->name('create');
        Route::post('/', [AdminMenuController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminMenuController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminMenuController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminMenuController::class, 'destroy'])->name('destroy');
        Route::post('/status', [AdminMenuController::class, 'status'])->name('status');
    });
    
    // Configuration Management
    Route::prefix('config')->name('config.')->group(function () {
        Route::get('/{group?}', [AdminConfigController::class, 'index'])->name('index');
        Route::post('/update', [AdminConfigController::class, 'update'])->name('update');
        Route::get('/create/{group?}', [AdminConfigController::class, 'create'])->name('create');
        Route::post('/', [AdminConfigController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminConfigController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminConfigController::class, 'updateConfig'])->name('update-config');
        Route::delete('/{id}', [AdminConfigController::class, 'destroy'])->name('destroy');
        Route::post('/quick-edit', [AdminConfigController::class, 'quickEdit'])->name('quick-edit');
        Route::post('/status', [AdminConfigController::class, 'status'])->name('status');
    });
});
