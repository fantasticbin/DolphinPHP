<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Log Model - Migrated from ThinkPHP to Laravel
 * 
 * Original: app\admin\model\Log
 * Stores system action logs
 * 
 * @package App\Models
 */
class Log extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'action_id',
        'action_name',
        'user_id',
        'username',
        'record_id',
        'model',
        'remark',
        'status',
        'ip',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            if (empty($log->ip)) {
                $log->ip = request()->ip();
            }
        });
    }

    /**
     * Get the user associated with the log.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the action associated with the log.
     */
    public function action()
    {
        return $this->belongsTo(Action::class, 'action_id');
    }

    /**
     * Scope a query by action name.
     */
    public function scopeByAction($query, $actionName)
    {
        return $query->where('action_name', $actionName);
    }

    /**
     * Scope a query by user ID.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Create a log entry
     *
     * @param string $actionName
     * @param string $model
     * @param int $recordId
     * @param int $userId
     * @param string $remark
     * @return static
     */
    public static function createLog($actionName, $model, $recordId, $userId, $remark = '')
    {
        $action = Action::where('name', $actionName)->first();
        
        return static::create([
            'action_id' => $action ? $action->id : 0,
            'action_name' => $actionName,
            'user_id' => $userId,
            'username' => get_nickname($userId),
            'record_id' => $recordId,
            'model' => $model,
            'remark' => $remark,
            'status' => 1,
            'ip' => request()->ip(),
        ]);
    }
}
