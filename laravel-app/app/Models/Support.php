<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Support Model - Customer support/service
 * 
 * @property int $id
 * @property string $title Support title
 * @property string $type Support type (QQ, WeChat, etc.)
 * @property string $account Account/contact info
 * @property string $avatar Avatar/icon
 * @property int $sort
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Support extends Model
{
    protected $table = 'cms_support';

    protected $fillable = [
        'title', 'type', 'account', 'avatar', 'sort', 'status'
    ];

    protected $casts = [
        'sort' => 'integer',
        'status' => 'integer',
    ];

    protected $attributes = [
        'sort' => 100,
        'status' => 1,
    ];

    /**
     * Scope: Active supports
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope: Ordered supports
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) {
            return '';
        }

        // If already full URL, return as is
        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }

        // Otherwise, prepend asset URL
        return asset($this->avatar);
    }
}
