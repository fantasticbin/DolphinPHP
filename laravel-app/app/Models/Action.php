<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Action Model - Migrated from ThinkPHP to Laravel
 * 
 * Original: app\admin\model\Action
 * Stores action log entries
 * 
 * @package App\Models
 */
class Action extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_action';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'title',
        'remark',
        'rule',
        'log',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'log' => 'boolean',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope a query to only include active actions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include logged actions.
     */
    public function scopeLogged($query)
    {
        return $query->where('log', 1);
    }

    /**
     * Get logs for this action.
     */
    public function logs()
    {
        return $this->hasMany(Log::class, 'action_name', 'name');
    }
}
