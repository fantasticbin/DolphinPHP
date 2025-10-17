<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Link Model (Friendly Links)
 * Manages website friendly links
 */
class Link extends Model
{
    protected $table = 'cms_link';
    
    protected $fillable = [
        'title',
        'url',
        'logo',
        'description',
        'rating',
        'sort',
        'status',
    ];
    
    /**
     * Scope: Active links only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    /**
     * Scope: Order by rating
     */
    public function scopeByRating($query, $direction = 'desc')
    {
        return $query->orderBy('rating', $direction);
    }
    
    /**
     * Scope: Order by sort
     */
    public function scopeBySort($query)
    {
        return $query->orderBy('sort');
    }
    
    /**
     * Get active links ordered by rating and sort
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveLinks()
    {
        return self::active()
                   ->byRating()
                   ->bySort()
                   ->get();
    }
}
