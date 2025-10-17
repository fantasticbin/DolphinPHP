<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Message Model - Migrated from ThinkPHP to Laravel
 * 
 * Original: app\user\model\Message
 * Handles system messages between users
 * 
 * @package App\Models
 */
class Message extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_message';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uid_send',
        'uid_receive',
        'type',
        'title',
        'content',
        'url',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the sender of the message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'uid_send');
    }

    /**
     * Get the receiver of the message.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'uid_receive');
    }

    /**
     * Scope a query to only include unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope a query to only include read messages.
     */
    public function scopeRead($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query for messages received by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('uid_receive', $userId);
    }

    /**
     * Scope a query for messages sent by user.
     */
    public function scopeFromUser($query, $userId)
    {
        return $query->where('uid_send', $userId);
    }

    /**
     * Get unread message count for current user
     *
     * @param int $userId
     * @return int
     */
    public static function getUnreadCount($userId)
    {
        return static::where('uid_receive', $userId)
            ->where('status', 0)
            ->count();
    }

    /**
     * Mark message as read
     *
     * @return bool
     */
    public function markAsRead()
    {
        return $this->update(['status' => 1]);
    }

    /**
     * Mark message as unread
     *
     * @return bool
     */
    public function markAsUnread()
    {
        return $this->update(['status' => 0]);
    }
}
