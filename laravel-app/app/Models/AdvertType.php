<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Advert Type Model
 * Advertisement positions/types
 */
class AdvertType extends Model
{
    protected $table = 'cms_advert_type';
    
    protected $fillable = [
        'title',
        'name',
        'description',
        'width',
        'height',
        'status',
    ];
    
    /**
     * Relationship: Adverts in this type
     */
    public function adverts()
    {
        return $this->hasMany(Advert::class, 'type');
    }
    
    /**
     * Scope: Active types only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    /**
     * Get all active advert types
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveTypes()
    {
        return self::active()->get();
    }
}
