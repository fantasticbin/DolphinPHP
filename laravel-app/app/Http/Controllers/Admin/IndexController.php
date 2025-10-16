<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Index Controller - Migrated from ThinkPHP to Laravel
 * 
 * Original: app\admin\controller\Index
 * Handles admin dashboard and common operations
 * 
 * @package App\Http\Controllers\Admin
 */
class IndexController extends AdminController
{
    /**
     * Display admin dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = User::find(1);
        $defaultPass = false;

        if ($this->getUserId() == 1 && $user && Hash::check('admin', $user->password)) {
            $defaultPass = true;
        }

        return view('admin.index', [
            'defaultPass' => $defaultPass,
        ]);
    }

    /**
     * Clear system cache
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function wipeCache(Request $request)
    {
        $cacheTypes = config('system.wipe_cache_type', []);

        if (empty($cacheTypes)) {
            return $this->error('请在系统设置中选择需要清除的缓存类型');
        }

        foreach ($cacheTypes as $type) {
            switch ($type) {
                case 'TEMP_PATH':
                    $files = Storage::files('temp');
                    Storage::delete($files);
                    break;
                    
                case 'LOG_PATH':
                    $directories = Storage::directories('logs');
                    foreach ($directories as $dir) {
                        Storage::deleteDirectory($dir);
                    }
                    break;
                    
                case 'CACHE_PATH':
                    Cache::flush();
                    break;
            }
        }

        return $this->success('清空成功');
    }

    /**
     * Display user profile form
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        $user = User::findOrFail($this->getUserId());
        
        return view('admin.profile', [
            'user' => $user,
        ]);
    }

    /**
     * Update user profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'nickname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|max:20',
        ]);

        $user = User::findOrFail($this->getUserId());
        
        $data = $request->only(['nickname', 'email', 'mobile', 'avatar']);
        
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return $this->success('编辑成功');
    }

    /**
     * Check for version updates
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUpdate()
    {
        $params = array_merge(config('dolphin', []), [
            'domain' => request()->getHost(),
            'website' => config('app.name'),
            'ip' => request()->server('SERVER_ADDR'),
            'php_os' => PHP_OS,
            'php_version' => PHP_VERSION,
            'mysql_version' => '8.0', // Would query actual MySQL version
            'server_software' => request()->server('SERVER_SOFTWARE'),
        ]);

        // This would make actual HTTP request to check for updates
        // For now, return a static response
        return response()->json([
            'update' => '',
            'auth' => 'Authorized',
        ]);
    }
}
