<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\IndexController as AdminIndexController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Admin\ConfigController as AdminConfigController;
use App\Http\Controllers\Admin\AttachmentController;
use App\Http\Controllers\Admin\IconController;
use App\Http\Controllers\Admin\AjaxController;
use App\Http\Controllers\Admin\ActionController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\HookController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\PacketController;
use App\Http\Controllers\Admin\DatabaseController;
use App\Http\Controllers\Admin\SystemController;
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
    
    // Attachment Management
    Route::prefix('attachment')->name('attachment.')->group(function () {
        Route::get('/', [AttachmentController::class, 'index'])->name('index');
        Route::post('/upload', [AttachmentController::class, 'upload'])->name('upload');
        Route::get('/{id}', [AttachmentController::class, 'show'])->name('show');
        Route::delete('/{id}', [AttachmentController::class, 'destroy'])->name('destroy');
    });
    
    // Icon Library Management
    Route::prefix('icon')->name('icon.')->group(function () {
        Route::get('/', [IconController::class, 'index'])->name('index');
        Route::post('/', [IconController::class, 'store'])->name('store');
        Route::put('/{id}', [IconController::class, 'update'])->name('update');
        Route::delete('/{id}', [IconController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/items', [IconController::class, 'items'])->name('items');
        Route::post('/{id}/reload', [IconController::class, 'reload'])->name('reload');
        Route::post('/status', [IconController::class, 'status'])->name('status');
    });
    
    // Ajax Utilities
    Route::prefix('ajax')->name('ajax.')->group(function () {
        Route::get('/get-level-data', [AjaxController::class, 'getLevelData'])->name('get-level-data');
        Route::get('/get-table', [AjaxController::class, 'getTable'])->name('get-table');
        Route::get('/get-table-info', [AjaxController::class, 'getTableInfo'])->name('get-table-info');
        Route::get('/get-menu-tree', [AjaxController::class, 'getMenuTree'])->name('get-menu-tree');
        Route::post('/clear-cache', [AjaxController::class, 'clearCache'])->name('clear-cache');
    });
    
    // Action Management
    Route::prefix('action')->name('action.')->group(function () {
        Route::get('/', [ActionController::class, 'index'])->name('index');
        Route::post('/', [ActionController::class, 'store'])->name('store');
        Route::put('/{id}', [ActionController::class, 'update'])->name('update');
        Route::delete('/{id}', [ActionController::class, 'destroy'])->name('destroy');
        Route::post('/status', [ActionController::class, 'status'])->name('status');
    });
    
    // Log Management
    Route::prefix('log')->name('log.')->group(function () {
        Route::get('/', [LogController::class, 'index'])->name('index');
        Route::get('/{id}', [LogController::class, 'show'])->name('show');
        Route::post('/export', [LogController::class, 'export'])->name('export');
        Route::delete('/bulk', [LogController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/clear', [LogController::class, 'clearOld'])->name('clear');
    });
    
    // Hook Management
    Route::prefix('hook')->name('hook.')->group(function () {
        Route::get('/', [HookController::class, 'index'])->name('index');
        Route::post('/', [HookController::class, 'store'])->name('store');
        Route::put('/{id}', [HookController::class, 'update'])->name('update');
        Route::delete('/{id}', [HookController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/bind', [HookController::class, 'bindPlugin'])->name('bind');
        Route::post('/status', [HookController::class, 'status'])->name('status');
    });
    
    // Plugin Management
    Route::prefix('plugin')->name('plugin.')->group(function () {
        Route::get('/', [PluginController::class, 'index'])->name('index');
        Route::post('/install', [PluginController::class, 'install'])->name('install');
        Route::get('/{name}/config', [PluginController::class, 'config'])->name('config');
        Route::post('/{name}/config', [PluginController::class, 'saveConfig'])->name('save-config');
        Route::post('/{name}/enable', [PluginController::class, 'enable'])->name('enable');
        Route::post('/{name}/disable', [PluginController::class, 'disable'])->name('disable');
        Route::delete('/{name}', [PluginController::class, 'uninstall'])->name('uninstall');
        Route::get('/{name}/hooks', [PluginController::class, 'hooks'])->name('hooks');
    });
    
    // Module Management
    Route::prefix('module')->name('module.')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('index');
        Route::get('/available', [ModuleController::class, 'available'])->name('available');
        Route::post('/install', [ModuleController::class, 'install'])->name('install');
        Route::get('/{name}/config', [ModuleController::class, 'config'])->name('config');
        Route::post('/{name}/config', [ModuleController::class, 'saveConfig'])->name('save-config');
        Route::post('/{name}/enable', [ModuleController::class, 'enable'])->name('enable');
        Route::post('/{name}/disable', [ModuleController::class, 'disable'])->name('disable');
        Route::delete('/{name}', [ModuleController::class, 'uninstall'])->name('uninstall');
        Route::post('/sort', [ModuleController::class, 'sort'])->name('sort');
    });
    
    // Packet Management
    Route::prefix('packet')->name('packet.')->group(function () {
        Route::get('/', [PacketController::class, 'index'])->name('index');
        Route::get('/available', [PacketController::class, 'available'])->name('available');
        Route::post('/install', [PacketController::class, 'install'])->name('install');
        Route::delete('/{name}', [PacketController::class, 'uninstall'])->name('uninstall');
        Route::post('/sort', [PacketController::class, 'sort'])->name('sort');
    });
    
    // Database Management
    Route::prefix('database')->name('database.')->group(function () {
        Route::get('/tables', [DatabaseController::class, 'tables'])->name('tables');
        Route::get('/table-info/{table}', [DatabaseController::class, 'tableInfo'])->name('table-info');
        Route::post('/optimize', [DatabaseController::class, 'optimize'])->name('optimize');
        Route::post('/repair', [DatabaseController::class, 'repair'])->name('repair');
        Route::post('/backup', [DatabaseController::class, 'backup'])->name('backup');
        Route::get('/backups', [DatabaseController::class, 'backups'])->name('backups');
        Route::post('/restore', [DatabaseController::class, 'restore'])->name('restore');
        Route::delete('/backup', [DatabaseController::class, 'deleteBackup'])->name('delete-backup');
        Route::get('/download/{filename}', [DatabaseController::class, 'download'])->name('download');
    });
    
    // System Management
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/info', [SystemController::class, 'info'])->name('info');
        Route::post('/clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
        Route::get('/cache-size', [SystemController::class, 'cacheSize'])->name('cache-size');
        Route::post('/maintenance', [SystemController::class, 'maintenance'])->name('maintenance');
        Route::get('/logs', [SystemController::class, 'logs'])->name('logs');
        Route::post('/clear-logs', [SystemController::class, 'clearLogs'])->name('clear-logs');
    });
});
