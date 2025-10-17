<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Hook Model - Migrated from ThinkPHP to Laravel
 * 
 * Original: app\admin\model\Hook
 * Manages system hooks for plugin integration
 * 
 * @package App\Models
 */
class Hook extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_hook';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'plugin',
        'description',
        'system',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'system' => 'boolean',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get plugins attached to this hook.
     */
    public function plugins()
    {
        return $this->hasMany(HookPlugin::class, 'hook', 'name');
    }

    /**
     * Scope a query to only include active hooks.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include system hooks.
     */
    public function scopeSystem($query)
    {
        return $query->where('system', 1);
    }

    /**
     * Scope a query to only include custom hooks.
     */
    public function scopeCustom($query)
    {
        return $query->where('system', 0);
    }

    /**
     * Add hooks from plugin
     *
     * @param array $hooks
     * @param string $pluginName
     * @return bool
     */
    public static function addHooks(array $hooks, $pluginName = '')
    {
        $data = [];
        
        foreach ($hooks as $name => $description) {
            if (is_numeric($name)) {
                $name = $description;
                $description = '';
            }
            
            // Skip if hook already exists
            if (static::where('name', $name)->exists()) {
                continue;
            }
            
            $data[] = [
                'name' => $name,
                'plugin' => $pluginName,
                'description' => $description,
                'system' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (!empty($data)) {
            return static::insert($data);
        }
        
        return true;
    }

    /**
     * Delete hooks by plugin name
     *
     * @param string $pluginName
     * @return bool
     */
    public static function deleteByPlugin($pluginName)
    {
        return static::where('plugin', $pluginName)->where('system', 0)->delete();
    }
}
