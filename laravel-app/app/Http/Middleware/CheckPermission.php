<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Role;

/**
 * Permission Check Middleware
 * 
 * Checks if user has permission to access specific resources
 */
class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = auth()->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['code' => 0, 'message' => '未登录'], 401);
            }
            return redirect()->route('login');
        }

        // Super admin (role_id = 1) has all permissions
        if ($user->role && $user->role->id == 1) {
            return $next($request);
        }

        // Check if user has required permissions
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($user, $permission)) {
                if ($request->expectsJson()) {
                    return response()->json(['code' => 0, 'message' => '权限不足'], 403);
                }
                abort(403, '您没有执行此操作的权限');
            }
        }

        return $next($request);
    }

    /**
     * Check if user has permission
     *
     * @param mixed $user
     * @param string $permission
     * @return bool
     */
    protected function hasPermission($user, $permission): bool
    {
        if (!$user->role) {
            return false;
        }

        // Get role permissions (stored as JSON in access field)
        $rolePermissions = $user->role->access;
        
        if (is_string($rolePermissions)) {
            $rolePermissions = json_decode($rolePermissions, true);
        }

        if (!is_array($rolePermissions)) {
            return false;
        }

        return in_array($permission, $rolePermissions);
    }
}
