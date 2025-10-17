<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Page Model (Single Pages)
 * For standalone pages like About Us, Contact, etc.
 */
class Page extends Model
{
    protected $table = 'cms_page';
    
    protected $fillable = [
        'title',
        'keywords',
        'description',
        'content',
        'cover',
        'template',
        'author',
        'views',
        'sort',
        'status',
    ];
    
    /**
     * Scope: Active pages only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    /**
     * Get page title list with caching
     *
     * @return array
     */
    public static function getTitleList()
    {
        return Cache::remember('cms_page_title_list', 3600, function () {
            return self::active()
                       ->pluck('title', 'id')
                       ->toArray();
        });
    }
    
    /**
     * Increment page views
     */
    public function incrementViews()
    {
        $this->increment('views');
    }
    
    /**
     * Clear page cache
     */
    public static function clearCache()
    {
        Cache::forget('cms_page_title_list');
    }
}
