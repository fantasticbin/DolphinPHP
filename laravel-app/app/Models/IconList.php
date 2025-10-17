<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Icon List Model
 * Individual icons within an icon library
 */
class IconList extends Model
{
    protected $table = 'admin_icon_list';
    
    public $timestamps = false;
    
    protected $fillable = [
        'icon_id',
        'title',
        'class',
        'code',
    ];
    
    /**
     * Relationship: Parent icon library
     */
    public function icon()
    {
        return $this->belongsTo(Icon::class, 'icon_id');
    }
}
