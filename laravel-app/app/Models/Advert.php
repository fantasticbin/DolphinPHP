<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Advert Model
 * Advertisement management
 */
class Advert extends Model
{
    protected $table = 'cms_advert';
    
    protected $fillable = [
        'type',
        'title',
        'content',
        'image',
        'url',
        'target',
        'start_time',
        'end_time',
        'clicks',
        'sort',
        'status',
    ];
    
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
    
    /**
     * Relationship: Parent advert type
     */
    public function advertType()
    {
        return $this->belongsTo(AdvertType::class, 'type');
    }
    
    /**
     * Scope: Active adverts only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    /**
     * Scope: Current date range
     */
    public function scopeInDateRange($query)
    {
        $now = Carbon::now();
        return $query->where(function ($q) use ($now) {
            $q->where('start_time', '<=', $now)
              ->orWhereNull('start_time')
              ->orWhere('start_time', '0000-00-00 00:00:00');
        })->where(function ($q) use ($now) {
            $q->where('end_time', '>=', $now)
              ->orWhereNull('end_time')
              ->orWhere('end_time', '0000-00-00 00:00:00');
        });
    }
    
    /**
     * Scope: By type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    /**
     * Increment click count
     */
    public function incrementClicks()
    {
        $this->increment('clicks');
    }
    
    /**
     * Get active adverts for a specific type
     *
     * @param int $type Type ID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveByType($type)
    {
        return self::active()
                   ->inDateRange()
                   ->byType($type)
                   ->orderBy('sort')
                   ->get();
    }
    
    /**
     * Accessor: Format start time for display
     */
    public function getStartTimeDisplayAttribute()
    {
        return $this->start_time ? $this->start_time->format('Y-m-d') : '';
    }
    
    /**
     * Accessor: Format end time for display
     */
    public function getEndTimeDisplayAttribute()
    {
        return $this->end_time ? $this->end_time->format('Y-m-d') : '';
    }
}
