<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Menu Model - Migrated from ThinkPHP to Laravel
 * 
 * Original: app\admin\model\Menu
 * @package App\Models
 */
class Menu extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_menu';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pid',
        'module',
        'title',
        'icon',
        'url_value',
        'url_type',
        'url_target',
        'online_hide',
        'status',
        'sort',
        'params',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pid' => 'integer',
        'online_hide' => 'boolean',
        'status' => 'boolean',
        'sort' => 'integer',
        'params' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the parent menu.
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'pid');
    }

    /**
     * Get the child menus.
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'pid');
    }

    /**
     * Scope a query to only include active menus.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include top level menus.
     */
    public function scopeTopLevel($query)
    {
        return $query->where('pid', 0);
    }

    /**
     * Scope a query by module.
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Get menus ordered by sort.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * Get menu tree structure
     * 
     * @param int $pid Parent ID
     * @param array $loaded Track loaded IDs to prevent cycles
     * @return \Illuminate\Support\Collection
     */
    public static function getTree($pid = 0, array &$loaded = [])
    {
        if (in_array($pid, $loaded)) {
            return collect([]);
        }
        
        $loaded[] = $pid;
        
        return static::where('pid', $pid)
            ->active()
            ->ordered()
            ->get()
            ->map(function ($menu) use (&$loaded) {
                $menu->children = static::getTree($menu->id, $loaded);
                return $menu;
            });
    }

    /**
     * Get all child IDs recursively
     * 
     * @param int $pid Parent ID
     * @param int $maxDepth Maximum recursion depth
     * @param array $loaded Track loaded IDs to prevent cycles
     * @return array
     */
    public static function getChildIds($pid, $maxDepth = 10, array &$loaded = [])
    {
        if ($maxDepth <= 0 || in_array($pid, $loaded)) {
            return [];
        }
        
        $loaded[] = $pid;
        $ids = static::where('pid', $pid)->pluck('id')->toArray();
        
        foreach ($ids as $id) {
            $ids = array_merge($ids, static::getChildIds($id, $maxDepth - 1, $loaded));
        }
        
        return $ids;
    }
}
