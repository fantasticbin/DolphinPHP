<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Module Model
 * Manages application modules
 */
class Module extends Model
{
    protected $table = 'admin_module';
    
    protected $fillable = [
        'name',
        'title',
        'icon',
        'description',
        'author',
        'author_url',
        'config',
        'access',
        'version',
        'identifier',
        'admin',
        'system_module',
        'sort',
        'status',
    ];
    
    protected $casts = [
        'config' => 'array',
        'access' => 'array',
    ];
    
    /**
     * Get all module names and titles
     *
     * @return array
     */
    public static function getModule()
    {
        return Cache::remember('modules', 3600, function () {
            return self::where('status', '>=', 0)
                       ->orderBy('id')
                       ->pluck('title', 'name')
                       ->toArray();
        });
    }
    
    /**
     * Get module configuration
     *
     * @param string $name Module name
     * @param string $item Specific config item(s)
     * @return mixed
     */
    public static function getConfig($name = '', $item = '')
    {
        if (empty($name)) {
            $name = request()->segment(1, 'admin');
        }
        
        $config = Cache::remember("module_config_{$name}", 3600, function () use ($name) {
            $config = self::where('name', $name)->value('config');
            return $config ? json_decode($config, true) : [];
        });
        
        if (empty($item)) {
            return $config;
        }
        
        $items = explode(',', $item);
        if (count($items) == 1) {
            return $config[$item] ?? '';
        }
        
        $result = [];
        foreach ($items as $i) {
            $result[$i] = $config[$i] ?? '';
        }
        return $result;
    }
    
    /**
     * Set module configuration
     *
     * @param string $name Module name or "module.item"
     * @param mixed $value Configuration value
     * @return bool
     */
    public static function setConfig($name = '', $value = '')
    {
        $item = '';
        if (strpos($name, '.')) {
            list($name, $item) = explode('.', $name, 2);
        }
        
        $module = self::where('name', $name)->first();
        if (!$module) {
            return false;
        }
        
        $config = $module->config ?? [];
        
        if ($item === '') {
            // Batch update
            if (!is_array($value) || empty($value)) {
                return false;
            }
            $config = array_merge($config, $value);
        } else {
            // Update single value
            $config[$item] = $value;
        }
        
        $module->config = $config;
        $saved = $module->save();
        
        if ($saved) {
            Cache::forget("module_config_{$name}");
        }
        
        return $saved;
    }
    
    /**
     * Get module info from file
     *
     * @param string $name Module name
     * @return array
     */
    public static function getInfoFromFile($name = '')
    {
        if (empty($name)) {
            return [];
        }
        
        $infoFile = base_path("application/{$name}/info.php");
        if (File::exists($infoFile)) {
            return include $infoFile;
        }
        
        return [];
    }
    
    /**
     * Get module menus from file
     *
     * @param string $name Module name
     * @return array
     */
    public static function getMenusFromFile($name = '')
    {
        if (empty($name)) {
            return [];
        }
        
        $menusFile = base_path("application/{$name}/menus.php");
        if (File::exists($menusFile)) {
            return include $menusFile;
        }
        
        return [];
    }
    
    /**
     * Clear module cache
     */
    public static function clearCache()
    {
        Cache::forget('modules');
        Cache::forget('module_all');
    }
}
