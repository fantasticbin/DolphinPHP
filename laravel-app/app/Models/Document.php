<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Document Model - CMS documents/articles
 * 
 * @property int $id
 * @property int $cid Column ID
 * @property int $uid User ID
 * @property int $model Model ID
 * @property string $title
 * @property string $summary
 * @property string $content
 * @property string $flag Custom flags
 * @property int $view View count
 * @property int $sort
 * @property int $status
 * @property int $trash Trash status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Document extends Model
{
    protected $table = 'cms_document';

    protected $fillable = [
        'cid', 'uid', 'model', 'title', 'summary', 'content',
        'flag', 'view', 'sort', 'status', 'trash'
    ];

    protected $casts = [
        'cid' => 'integer',
        'uid' => 'integer',
        'model' => 'integer',
        'view' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
        'trash' => 'integer',
    ];

    protected $attributes = [
        'view' => 0,
        'sort' => 100,
        'status' => 1,
        'trash' => 0,
    ];

    /**
     * Relationship: Document belongs to Column
     */
    public function column()
    {
        return $this->belongsTo(Column::class, 'cid');
    }

    /**
     * Relationship: Document belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'uid');
    }

    /**
     * Relationship: Document belongs to CMS Model
     */
    public function cmsModel()
    {
        return $this->belongsTo(CmsModel::class, 'model');
    }

    /**
     * Scope: Active documents
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1)->where('trash', 0);
    }

    /**
     * Scope: By column
     */
    public function scopeByColumn($query, $columnId)
    {
        return $query->where('cid', $columnId);
    }

    /**
     * Increment view count
     */
    public function incrementView()
    {
        return $this->increment('view');
    }

    /**
     * Move to trash
     */
    public function moveToTrash()
    {
        return $this->update(['trash' => 1]);
    }

    /**
     * Restore from trash
     */
    public function restore()
    {
        return $this->update(['trash' => 0]);
    }
}
