<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Nav Model - CMS navigation
 * 
 * @property int $id
 * @property string $title Navigation title
 * @property string $url
 * @property string $target Link target
 * @property string $icon
 * @property int $sort
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Nav extends Model
{
    protected $table = 'cms_nav';

    protected $fillable = [
        'title', 'url', 'target', 'icon', 'sort', 'status'
    ];

    protected $casts = [
        'sort' => 'integer',
        'status' => 'integer',
    ];

    protected $attributes = [
        'target' => '_self',
        'sort' => 100,
        'status' => 1,
    ];

    /**
     * Scope: Active navigations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope: Ordered navigations
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'asc')->orderBy('id', 'asc');
    }
}
