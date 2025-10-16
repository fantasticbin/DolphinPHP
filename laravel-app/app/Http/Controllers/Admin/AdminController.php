<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Admin Base Controller - Migrated from ThinkPHP to Laravel
 * 
 * Original: app\admin\controller\Admin
 * This is the base controller for all admin controllers
 * 
 * @package App\Http\Controllers\Admin
 */
class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            // Check if user has admin access
            if (!$this->checkAdminAccess()) {
                abort(403, '权限不足！');
            }
            
            return $next($request);
        });
    }

    /**
     * Check if current user has admin access
     *
     * @return bool
     */
    protected function checkAdminAccess(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Check if user has a role and the role has access
        if (!$user->role || !$user->role->access) {
            return false;
        }

        return true;
    }

    /**
     * Get current user ID
     *
     * @return int|null
     */
    protected function getUserId(): ?int
    {
        return Auth::id();
    }

    /**
     * Success response
     *
     * @param string $message
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success(string $message = '操作成功', $data = null)
    {
        return response()->json([
            'code' => 1,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Error response
     *
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error(string $message = '操作失败', int $code = 0)
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
        ]);
    }

    /**
     * Clear system cache
     *
     * @return void
     */
    protected function clearCache(): void
    {
        Cache::flush();
    }
}
