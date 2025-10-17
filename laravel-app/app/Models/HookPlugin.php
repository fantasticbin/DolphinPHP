<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * HookPlugin Model - Migrated from ThinkPHP to Laravel
 * 
 * Original: app\admin\model\HookPlugin
 * Links plugins to hooks
 * 
 * @package App\Models
 */
class HookPlugin extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_hook_plugin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hook',
        'plugin',
        'sort',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sort' => 'integer',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the hook that owns the plugin.
     */
    public function hookModel()
    {
        return $this->belongsTo(Hook::class, 'hook', 'name');
    }

    /**
     * Scope a query to only include active hook plugins.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query by hook name.
     */
    public function scopeByHook($query, $hook)
    {
        return $query->where('hook', $hook);
    }

    /**
     * Scope a query by plugin name.
     */
    public function scopeByPlugin($query, $plugin)
    {
        return $query->where('plugin', $plugin);
    }

    /**
     * Scope a query ordered by sort.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }
}
