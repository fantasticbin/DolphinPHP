<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Admin Authentication Middleware
 * 
 * Checks if user is authenticated and has admin access
 */
class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['code' => 0, 'message' => '未登录'], 401);
            }
            return redirect()->route('login')->with('error', '请先登录');
        }

        $user = Auth::user();

        // Check if user has admin role
        if (!$user->role || !$user->role->access) {
            if ($request->expectsJson()) {
                return response()->json(['code' => 0, 'message' => '权限不足'], 403);
            }
            abort(403, '您没有访问后台的权限');
        }

        // Check if user is active
        if ($user->status != 1) {
            Auth::logout();
            if ($request->expectsJson()) {
                return response()->json(['code' => 0, 'message' => '账号已被禁用'], 403);
            }
            return redirect()->route('login')->with('error', '您的账号已被禁用');
        }

        return $next($request);
    }
}
