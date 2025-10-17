<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Attachment Model - Migrated from ThinkPHP to Laravel
 * 
 * Original: app\admin\model\Attachment
 * Handles file uploads and attachments
 * 
 * @package App\Models
 */
class Attachment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_attachment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'path',
        'url',
        'mime',
        'ext',
        'size',
        'md5',
        'sha1',
        'driver',
        'width',
        'height',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the attachment.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope a query to only include active attachments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query by driver.
     */
    public function scopeByDriver($query, $driver)
    {
        return $query->where('driver', $driver);
    }

    /**
     * Scope a query by mime type.
     */
    public function scopeByMime($query, $mime)
    {
        return $query->where('mime', 'like', $mime.'%');
    }

    /**
     * Get file path by attachment ID(s)
     *
     * @param int|array $id Attachment ID(s)
     * @param int $type 0=with public path, 1=database path only
     * @return string|array|null
     */
    public static function getFilePath($id, $type = 0)
    {
        if (is_array($id)) {
            $attachments = static::whereIn('id', $id)->get();
            $paths = [];
            
            foreach ($attachments as $attachment) {
                if ($attachment->driver == 'local') {
                    $paths[$attachment->id] = ($type == 0 ? public_path() : '') . $attachment->path;
                } else {
                    $paths[$attachment->id] = $attachment->path;
                }
            }
            
            return $paths;
        } else {
            $attachment = static::find($id);
            
            if (!$attachment) {
                return null;
            }
            
            if ($attachment->driver == 'local') {
                return ($type == 0 ? public_path() : '') . $attachment->path;
            } else {
                return $attachment->path;
            }
        }
    }

    /**
     * Get file URL
     *
     * @return string
     */
    public function getFileUrl()
    {
        if ($this->driver == 'local') {
            return asset($this->path);
        }
        
        return $this->url ?: $this->path;
    }

    /**
     * Check if attachment is an image
     *
     * @return bool
     */
    public function isImage()
    {
        return str_starts_with($this->mime, 'image/');
    }

    /**
     * Get human readable file size
     *
     * @return string
     */
    public function getHumanSizeAttribute()
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->size;
        
        for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }
}
