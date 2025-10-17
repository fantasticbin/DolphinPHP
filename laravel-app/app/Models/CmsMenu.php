<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CmsMenu Model - CMS navigation menu
 * 
 * @property int $id
 * @property string $title
 * @property string $url
 * @property string $target Link target (_blank, _self, etc.)
 * @property int $sort
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CmsMenu extends Model
{
    protected $table = 'cms_menu';

    protected $fillable = [
        'title', 'url', 'target', 'sort', 'status'
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
     * Scope: Active menus
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope: Ordered menus
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'asc')->orderBy('id', 'asc');
    }
}
