<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Slider Model - Image sliders/carousels
 * 
 * @property int $id
 * @property string $title Slider title
 * @property string $image Image URL/path
 * @property string $url Link URL
 * @property string $target Link target
 * @property string $description
 * @property int $sort
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Slider extends Model
{
    protected $table = 'cms_slider';

    protected $fillable = [
        'title', 'image', 'url', 'target', 'description', 'sort', 'status'
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
     * Scope: Active sliders
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope: Ordered sliders
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return '';
        }

        // If already full URL, return as is
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        // Otherwise, prepend asset URL
        return asset($this->image);
    }
}
