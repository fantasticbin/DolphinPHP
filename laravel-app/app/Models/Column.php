<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Column Model (CMS Content Columns/Categories)
 * Hierarchical structure for organizing content
 */
class Column extends Model
{
    protected $table = 'cms_column';
    
    protected $fillable = [
        'pid',
        'model',
        'name',
        'title',
        'keywords',
        'description',
        'content',
        'cover',
        'url',
        'list_template',
        'detail_template',
        'page_template',
        'type',
        'list_row',
        'sort',
        'status',
    ];
    
    /**
     * Self-referencing relationship: parent column
     */
    public function parent()
    {
        return $this->belongsTo(Column::class, 'pid');
    }
    
    /**
     * Self-referencing relationship: child columns
     */
    public function children()
    {
        return $this->hasMany(Column::class, 'pid');
    }
    
    /**
     * Scope: Active columns only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    /**
     * Get column list with caching
     *
     * @return array
     */
    public static function getList()
    {
        return Cache::remember('cms_column_list', 3600, function () {
            return self::active()
                       ->orderBy('sort')
                       ->get()
                       ->keyBy('id')
                       ->toArray();
        });
    }
    
    /**
     * Get tree-structured column list
     *
     * @param int $id Column ID to hide (with its children)
     * @param string|false $default Default first node text
     * @return array
     */
    public static function getTreeList($id = 0, $default = '顶级栏目')
    {
        $result = [];
        
        if ($default !== false) {
            $result[0] = $default;
        }
        
        // Exclude specified node and its children
        $query = self::active()->orderBy('pid')->orderBy('id');
        
        if ($id !== 0) {
            $hideIds = array_merge([$id], self::getChildsId($id));
            $query->whereNotIn('id', $hideIds);
        }
        
        $columns = $query->get(['id', 'pid', 'name'])->toArray();
        
        // Build tree structure
        $tree = self::buildTree($columns);
        
        foreach ($tree as $item) {
            $result[$item['id']] = str_repeat('　', $item['level']) . $item['name'];
        }
        
        return $result;
    }
    
    /**
     * Build tree structure with levels
     */
    private static function buildTree($items, $pid = 0, $level = 0, &$result = [])
    {
        foreach ($items as $item) {
            if ($item['pid'] == $pid) {
                $item['level'] = $level;
                $result[] = $item;
                self::buildTree($items, $item['id'], $level + 1, $result);
            }
        }
        return $result;
    }
    
    /**
     * Get all child column IDs recursively
     *
     * @param int $pid Parent ID
     * @param int $depth Current recursion depth
     * @param int $maxDepth Maximum recursion depth
     * @return array
     */
    public static function getChildsId($pid = 0, $depth = 0, $maxDepth = 10)
    {
        if ($depth >= $maxDepth) {
            return [];
        }
        
        $ids = self::where('pid', $pid)->pluck('id')->toArray();
        
        foreach ($ids as $value) {
            $ids = array_merge($ids, self::getChildsId($value, $depth + 1, $maxDepth));
        }
        
        return $ids;
    }
    
    /**
     * Get column info with caching
     *
     * @param int $cid Column ID
     * @return Column|null
     */
    public static function getInfo($cid = 0)
    {
        return Cache::remember("cms_column_info_{$cid}", 3600, function () use ($cid) {
            return self::find($cid);
        });
    }
    
    /**
     * Clear column cache
     */
    public static function clearCache()
    {
        Cache::forget('cms_column_list');
        Cache::tags(['cms_column'])->flush();
    }
}
