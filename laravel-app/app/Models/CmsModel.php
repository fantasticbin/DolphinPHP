<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * CmsModel Model - Content model definitions
 * 
 * @property int $id
 * @property string $title Model title
 * @property string $name Model name (identifier)
 * @property string $table Database table name
 * @property int $type Model type (1=attached, 2=independent)
 * @property string $description
 * @property int $sort
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CmsModel extends Model
{
    protected $table = 'cms_model';

    protected $fillable = [
        'title', 'name', 'table', 'type', 'description', 'sort', 'status'
    ];

    protected $casts = [
        'type' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
    ];

    protected $attributes = [
        'type' => 1,
        'sort' => 100,
        'status' => 1,
    ];

    /**
     * Relationship: Model has many Fields
     */
    public function fields()
    {
        return $this->hasMany(Field::class, 'model');
    }

    /**
     * Relationship: Model has many Documents
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'model');
    }

    /**
     * Scope: Active models
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get model list with cache
     */
    public static function getList()
    {
        return Cache::remember('cms_model_list', 3600, function () {
            return static::active()->get()->keyBy('id')->toArray();
        });
    }

    /**
     * Get model title list
     */
    public static function getTitleList($map = [])
    {
        return static::active()->where($map)->pluck('title', 'id')->toArray();
    }

    /**
     * Create model table
     */
    public function createTable(): bool
    {
        if (!$this->table) {
            return false;
        }

        try {
            if ($this->type == 2) {
                // Independent model table
                $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
                    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Document ID',
                    `cid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Column ID',
                    `uid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'User ID',
                    `model` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Model ID',
                    `title` varchar(256) NOT NULL DEFAULT '' COMMENT 'Title',
                    `created_at` timestamp NULL,
                    `updated_at` timestamp NULL,
                    `sort` int(11) NOT NULL DEFAULT 100 COMMENT 'Sort',
                    `status` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Status',
                    `view` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'View count',
                    `trash` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Trash',
                    PRIMARY KEY (`id`),
                    KEY `idx_cid` (`cid`),
                    KEY `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='{$this->title} model table'";
            } else {
                // Attached model table (extension)
                $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
                    `aid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Document ID',
                    PRIMARY KEY (`aid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='{$this->title} model extension table'";
            }

            DB::statement($sql);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete model table
     */
    public function deleteTable(): bool
    {
        if (!$this->table) {
            return false;
        }

        try {
            DB::statement("DROP TABLE IF EXISTS `{$this->table}`");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if table exists
     */
    public function tableExists(): bool
    {
        return $this->table && Schema::hasTable($this->table);
    }
}
