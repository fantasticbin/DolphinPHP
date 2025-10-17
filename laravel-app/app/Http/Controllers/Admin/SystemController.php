<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SystemController extends AdminController
{
    /**
     * Get system information
     */
    public function info()
    {
        $info = [
            'system' => [
                'os' => PHP_OS,
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'mysql_version' => $this->getMysqlVersion(),
                'upload_max_size' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'memory_limit' => ini_get('memory_limit'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
            ],
            'paths' => [
                'base' => base_path(),
                'storage' => storage_path(),
                'public' => public_path(),
                'cache' => storage_path('framework/cache'),
                'logs' => storage_path('logs'),
            ],
            'permissions' => [
                'storage' => is_writable(storage_path()),
                'cache' => is_writable(storage_path('framework/cache')),
                'logs' => is_writable(storage_path('logs')),
                'bootstrap' => is_writable(base_path('bootstrap/cache')),
            ],
            'extensions' => [
                'pdo' => extension_loaded('pdo'),
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl'),
                'gd' => extension_loaded('gd'),
                'curl' => extension_loaded('curl'),
                'fileinfo' => extension_loaded('fileinfo'),
                'zip' => extension_loaded('zip'),
            ],
        ];

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => $info
        ]);
    }

    /**
     * Get MySQL version
     */
    private function getMysqlVersion()
    {
        try {
            $result = \DB::select('SELECT VERSION() as version');
            return $result[0]->version ?? 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Clear all caches
     */
    public function clearCache(Request $request)
    {
        $type = $request->input('type', 'all');

        try {
            switch ($type) {
                case 'config':
                    Artisan::call('config:clear');
                    $msg = 'Configuration cache cleared';
                    break;
                
                case 'route':
                    Artisan::call('route:clear');
                    $msg = 'Route cache cleared';
                    break;
                
                case 'view':
                    Artisan::call('view:clear');
                    $msg = 'View cache cleared';
                    break;
                
                case 'cache':
                    Artisan::call('cache:clear');
                    $msg = 'Application cache cleared';
                    break;
                
                case 'all':
                default:
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Artisan::call('cache:clear');
                    Cache::flush();
                    $msg = 'All caches cleared';
                    break;
            }

            action_log('system_clear_cache', 'admin_system', 0, auth()->id(), "清除缓存：{$type}");

            return response()->json([
                'code' => 1,
                'msg' => $msg
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 0,
                'msg' => 'Clear cache failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get cache size
     */
    public function cacheSize()
    {
        $sizes = [
            'config' => $this->getDirectorySize(base_path('bootstrap/cache')),
            'views' => $this->getDirectorySize(storage_path('framework/views')),
            'cache' => $this->getDirectorySize(storage_path('framework/cache')),
            'sessions' => $this->getDirectorySize(storage_path('framework/sessions')),
            'logs' => $this->getDirectorySize(storage_path('logs')),
        ];

        $total = array_sum($sizes);

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => [
                'sizes' => $sizes,
                'total' => $total,
                'formatted' => $this->formatBytes($total)
            ]
        ]);
    }

    /**
     * Get directory size
     */
    private function getDirectorySize($path)
    {
        if (!File::isDirectory($path)) {
            return 0;
        }

        $size = 0;
        $files = File::allFiles($path);

        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Run maintenance mode
     */
    public function maintenance(Request $request)
    {
        $action = $request->input('action'); // 'up' or 'down'

        try {
            if ($action === 'down') {
                Artisan::call('down', [
                    '--render' => 'errors::503'
                ]);
                $msg = 'Maintenance mode enabled';
            } else {
                Artisan::call('up');
                $msg = 'Maintenance mode disabled';
            }

            action_log('system_maintenance', 'admin_system', 0, auth()->id(), "维护模式：{$action}");

            return response()->json([
                'code' => 1,
                'msg' => $msg
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 0,
                'msg' => 'Operation failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get system logs
     */
    public function logs(Request $request)
    {
        $file = $request->input('file', 'laravel.log');
        $lines = $request->input('lines', 100);

        $logPath = storage_path('logs/' . $file);

        if (!File::exists($logPath)) {
            return response()->json([
                'code' => 0,
                'msg' => 'Log file not found'
            ]);
        }

        $content = File::get($logPath);
        $logLines = explode("\n", $content);
        $logLines = array_slice($logLines, -$lines);

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => [
                'file' => $file,
                'lines' => array_filter($logLines),
                'size' => File::size($logPath)
            ]
        ]);
    }

    /**
     * Clear logs
     */
    public function clearLogs(Request $request)
    {
        $file = $request->input('file');

        if ($file) {
            $logPath = storage_path('logs/' . $file);
            if (File::exists($logPath)) {
                File::delete($logPath);
            }
        } else {
            // Clear all logs
            $files = File::files(storage_path('logs'));
            foreach ($files as $file) {
                File::delete($file);
            }
        }

        action_log('system_clear_logs', 'admin_system', 0, auth()->id(), "清除日志文件");

        return response()->json([
            'code' => 1,
            'msg' => 'Logs cleared successfully'
        ]);
    }
}
