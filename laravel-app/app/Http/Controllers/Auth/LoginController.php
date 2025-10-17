<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Authentication Controller - Migrated from ThinkPHP to Laravel
 * 
 * Handles user authentication (login/logout)
 * 
 * @package App\Http\Controllers\Auth
 */
class LoginController extends Controller
{
    /**
     * Show the login form
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        // Redirect to dashboard if already authenticated
        if (Auth::check()) {
            return redirect()->route('admin.index');
        }

        return view('auth.login');
    }

    /**
     * Handle a login request
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => '请输入用户名',
            'password.required' => '请输入密码',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'code' => 0,
                    'message' => $validator->errors()->first()
                ]);
            }
            return back()->withErrors($validator)->withInput();
        }

        $username = $request->input('username');
        $password = $request->input('password');
        $remember = $request->input('remember', false);

        // Find user by username, email, or mobile
        $user = $this->findUser($username);

        if (!$user) {
            $message = '账号或密码错误';
            if ($request->expectsJson()) {
                return response()->json(['code' => 0, 'message' => $message]);
            }
            return back()->withErrors(['username' => $message])->withInput();
        }

        // Check if user is active
        if ($user->status != 1) {
            $message = '账号已被禁用';
            if ($request->expectsJson()) {
                return response()->json(['code' => 0, 'message' => $message]);
            }
            return back()->withErrors(['username' => $message])->withInput();
        }

        // Check if user has role
        if (!$user->role || $user->role->id == 0) {
            $message = '您还未分配角色，无法登录';
            if ($request->expectsJson()) {
                return response()->json(['code' => 0, 'message' => $message]);
            }
            return back()->withErrors(['username' => $message])->withInput();
        }

        // Check if role has backend access
        if (!$user->role->access) {
            $message = '您所在的角色无法访问后台';
            if ($request->expectsJson()) {
                return response()->json(['code' => 0, 'message' => $message]);
            }
            return back()->withErrors(['username' => $message])->withInput();
        }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            $message = '账号或密码错误';
            if ($request->expectsJson()) {
                return response()->json(['code' => 0, 'message' => $message]);
            }
            return back()->withErrors(['username' => $message])->withInput();
        }

        // Login the user
        Auth::login($user, $remember);

        // Update last login time if column exists
        try {
            $user->update(['last_login_time' => now()]);
        } catch (\Exception $e) {
            // Column might not exist yet
        }

        // Log the action
        action_log('user_login', 'admin_user', $user->id, $user->id, '用户登录');

        if ($request->expectsJson()) {
            return response()->json([
                'code' => 1,
                'message' => '登录成功',
                'data' => ['redirect' => route('admin.index')]
            ]);
        }

        return redirect()->intended(route('admin.index'));
    }

    /**
     * Handle a logout request
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $userId = Auth::id();
        
        if ($userId) {
            action_log('user_logout', 'admin_user', $userId, $userId, '用户退出');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'code' => 1,
                'message' => '退出成功'
            ]);
        }

        return redirect()->route('login');
    }

    /**
     * Find user by username, email, or mobile
     *
     * @param string $username
     * @return User|null
     */
    protected function findUser($username)
    {
        // Check if it's an email
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return User::where('email', $username)->first();
        }

        // Check if it's a mobile number
        if (preg_match('/^1\d{10}$/', $username)) {
            return User::where('mobile', $username)->first();
        }

        // Default to username
        return User::where('username', $username)->first();
    }
}
