<?php

/**
 * Helper Functions - Migrated from ThinkPHP to Laravel
 * 
 * Common helper functions for DolphinPHP
 */

if (!function_exists('get_client_ip')) {
    /**
     * Get client IP address
     * 
     * @param int $type Return type (0=IP string, 1=IP long)
     * @return string|int
     */
    function get_client_ip($type = 0)
    {
        $ip = request()->ip();
        
        return $type === 1 ? ip2long($ip) : $ip;
    }
}

if (!function_exists('admin_url')) {
    /**
     * Generate admin URL
     * 
     * @param string $url URL path
     * @param array $params URL parameters
     * @return string
     */
    function admin_url($url, $params = [])
    {
        return route('admin.' . str_replace('/', '.', $url), $params);
    }
}

if (!function_exists('home_url')) {
    /**
     * Generate home URL
     * 
     * @param string $url URL path
     * @param array $params URL parameters
     * @return string
     */
    function home_url($url, $params = [])
    {
        return route('home.' . str_replace('/', '.', $url), $params);
    }
}

if (!function_exists('get_nickname')) {
    /**
     * Get user nickname by ID
     * 
     * @param int $uid User ID
     * @return string
     */
    function get_nickname($uid)
    {
        $user = \App\Models\User::find($uid);
        return $user ? $user->nickname : '';
    }
}

if (!function_exists('action_log')) {
    /**
     * Record action log
     * 
     * @param string $action Action name
     * @param string $model Model name
     * @param int $recordId Record ID
     * @param int $userId User ID
     * @param string $remark Remark
     * @return bool
     */
    function action_log($action, $model, $recordId, $userId, $remark = '')
    {
        // Implementation would go here
        // This is a placeholder for the actual logging functionality
        \Illuminate\Support\Facades\Log::info("Action: {$action}, Model: {$model}, Record: {$recordId}, User: {$userId}, Remark: {$remark}");
        return true;
    }
}

if (!function_exists('get_browser_type')) {
    /**
     * Get browser type
     * 
     * @return string
     */
    function get_browser_type()
    {
        $agent = request()->header('User-Agent');
        
        if (stripos($agent, 'MSIE') !== false || stripos($agent, 'Trident') !== false) {
            return 'ie';
        }
        
        if (stripos($agent, 'Firefox') !== false) {
            return 'firefox';
        }
        
        if (stripos($agent, 'Chrome') !== false) {
            return 'chrome';
        }
        
        if (stripos($agent, 'Safari') !== false) {
            return 'safari';
        }
        
        return 'other';
    }
}

if (!function_exists('role_auth')) {
    /**
     * Set role permissions in session
     * 
     * @return void
     */
    function role_auth()
    {
        // Implementation would set current user's role permissions
        $user = auth()->user();
        if ($user) {
            // Load role relationship if not already loaded
            if (!$user->relationLoaded('role')) {
                $user->load('role');
            }
            
            if ($user->role) {
                session(['user_auth.role' => $user->role->id]);
            }
        }
    }
}

if (!function_exists('str_slug')) {
    /**
     * Generate a URL friendly "slug" from a given string
     * Laravel 11 uses Str::slug() but keeping this for compatibility
     * 
     * @param string $title
     * @param string $separator
     * @return string
     */
    function str_slug($title, $separator = '-')
    {
        return \Illuminate\Support\Str::slug($title, $separator);
    }
}

if (!function_exists('config_cache')) {
    /**
     * Get or set cached config value
     * 
     * @param string $name Config name
     * @param mixed $value Config value
     * @param int $expire Expiration in seconds
     * @return mixed
     */
    function config_cache($name, $value = null, $expire = 3600)
    {
        if ($value === null) {
            return \Illuminate\Support\Facades\Cache::get("config.{$name}");
        }
        
        return \Illuminate\Support\Facades\Cache::put("config.{$name}", $value, $expire);
    }
}
