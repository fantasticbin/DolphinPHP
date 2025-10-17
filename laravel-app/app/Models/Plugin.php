<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Plugin Model - Migrated from ThinkPHP to Laravel
 * 
 * Original: app\admin\model\Plugin
 * Manages system plugins
 * 
 * @package App\Models
 */
class Plugin extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_plugin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'title',
        'icon',
        'description',
        'author',
        'author_url',
        'version',
        'config',
        'admin',
        'bootstrap',
        'sort',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'config' => 'array',
        'admin' => 'boolean',
        'bootstrap' => 'boolean',
        'sort' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope a query to only include enabled plugins.
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include disabled plugins.
     */
    public function scopeDisabled($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope a query to only include admin plugins.
     */
    public function scopeAdminOnly($query)
    {
        return $query->where('admin', 1);
    }

    /**
     * Scope a query ordered by sort.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id', 'desc');
    }

    /**
     * Get plugin hooks.
     */
    public function hooks()
    {
        return $this->hasMany(HookPlugin::class, 'plugin', 'name');
    }

    /**
     * Check if plugin is installed
     *
     * @param string $name
     * @return bool
     */
    public static function isInstalled($name)
    {
        return static::where('name', $name)->exists();
    }

    /**
     * Get plugin by name
     *
     * @param string $name
     * @return static|null
     */
    public static function getByName($name)
    {
        return static::where('name', $name)->first();
    }
}
