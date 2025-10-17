<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Config Model - Migrated from ThinkPHP to Laravel
 * 
 * Original: app\admin\model\Config
 * Stores system configuration
 * 
 * @package App\Models
 */
class Config extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_config';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'name',
        'group',
        'type',
        'value',
        'options',
        'tip',
        'status',
        'sort',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
        'sort' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope a query to only include active configs.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query by group.
     */
    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Get configuration value by name
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function getValue($name, $default = null)
    {
        return Cache::remember("config.{$name}", 3600, function () use ($name, $default) {
            $config = static::where('name', $name)->where('status', 1)->first();
            return $config ? $config->value : $default;
        });
    }

    /**
     * Set configuration value
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public static function setValue($name, $value)
    {
        $result = static::where('name', $name)->update(['value' => $value]);
        
        if ($result) {
            Cache::forget("config.{$name}");
            Cache::forget('system_config');
        }
        
        return $result;
    }

    /**
     * Get all configurations as array
     *
     * @param string|null $group
     * @return array
     */
    public static function getAllConfigs($group = null)
    {
        $cacheKey = $group ? "config.group.{$group}" : 'system_config';
        
        return Cache::remember($cacheKey, 3600, function () use ($group) {
            $query = static::active();
            
            if ($group) {
                $query->byGroup($group);
            }
            
            return $query->pluck('value', 'name')->toArray();
        });
    }

    /**
     * Clear configuration cache
     *
     * @return void
     */
    public static function clearCache()
    {
        Cache::forget('system_config');
        Cache::tags(['config'])->flush();
    }
}
