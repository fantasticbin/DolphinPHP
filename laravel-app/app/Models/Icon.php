<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Icon Library Model
 * Manages icon libraries and their CSS URLs
 */
class Icon extends Model
{
    protected $table = 'admin_icon';
    
    protected $fillable = [
        'title',
        'name',
        'url',
        'sort',
        'status',
    ];
    
    /**
     * Relationship: Icons in this library
     */
    public function icons()
    {
        return $this->hasMany(IconList::class, 'icon_id')
                    ->select(['id', 'icon_id', 'title', 'class', 'code']);
    }
    
    /**
     * Scope: Active icons only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    /**
     * Get all icon URLs with validation
     *
     * @return array
     */
    public static function getUrls()
    {
        $list = self::active()
                    ->with('icons')
                    ->orderBy('sort')
                    ->get()
                    ->toArray();
        
        if (!$list) {
            return [];
        }
        
        foreach ($list as $key => $item) {
            // Ensure URL has protocol
            $url = substr($item['url'], 0, 4) == 'http' 
                   ? $item['url'] 
                   : 'http:' . $item['url'];
            
            $item['url'] = $url;
            
            // Generate HTML for icon display
            if (isset($item['icons']) && !empty($item['icons'])) {
                $html = '<ul class="js-icon-list items-push-2x text-center">';
                foreach ($item['icons'] as $icon) {
                    $html .= '<li title="' . htmlspecialchars($icon['title']) . '">';
                    $html .= '<i class="' . htmlspecialchars($icon['class']) . '"></i> ';
                    $html .= '<code>' . htmlspecialchars($icon['code']) . '</code>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
            } else {
                $html = '<p class="text-center text-muted">暂无图标</p>';
            }
            
            $item['html'] = $html;
            $list[$key] = $item;
        }
        
        return array_values($list);
    }
    
    /**
     * Get formatted file size
     */
    public function getFileSizeAttribute($value)
    {
        if (!$value) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }
        
        return round($value, 2) . ' ' . $units[$i];
    }
}
