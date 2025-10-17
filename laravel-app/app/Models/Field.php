<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Field Model - CMS custom fields
 * 
 * @property int $id
 * @property int $model Model ID
 * @property string $level Field level
 * @property string $name Field name
 * @property string $title Field title
 * @property string $define Field definition (SQL type)
 * @property string $type Field type (text, textarea, editor, etc.)
 * @property string $options Field options
 * @property string $value Default value
 * @property int $show Show in form (0=no, 1=yes)
 * @property int $status
 * @property int $sort
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Field extends Model
{
    protected $table = 'cms_field';

    protected $fillable = [
        'model', 'level', 'name', 'title', 'define', 'type',
        'options', 'value', 'show', 'status', 'sort'
    ];

    protected $casts = [
        'model' => 'integer',
        'show' => 'integer',
        'status' => 'integer',
        'sort' => 'integer',
    ];

    protected $attributes = [
        'show' => 1,
        'status' => 1,
        'sort' => 100,
    ];

    /**
     * Relationship: Field belongs to CMS Model
     */
    public function cmsModel()
    {
        return $this->belongsTo(CmsModel::class, 'model');
    }

    /**
     * Scope: Active fields
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope: By model
     */
    public function scopeByModel($query, $modelId)
    {
        return $query->where('model', $modelId);
    }

    /**
     * Scope: Visible in form
     */
    public function scopeVisible($query)
    {
        return $query->where('show', 1);
    }

    /**
     * Check if table exists for model
     */
    public function tableExists($modelId): bool
    {
        $cmsModel = CmsModel::find($modelId);
        if (!$cmsModel || !$cmsModel->table) {
            return false;
        }
        return Schema::hasTable($cmsModel->table);
    }

    /**
     * Create field in database table
     */
    public function createField(): bool
    {
        if (!$this->tableExists($this->model)) {
            return false;
        }

        $cmsModel = CmsModel::find($this->model);
        $tableName = $cmsModel->table;

        try {
            $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$this->name}` {$this->define} COMMENT '{$this->title}'";
            DB::statement($sql);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update field in database table
     */
    public function updateFieldInTable($oldName): bool
    {
        if (!$this->tableExists($this->model)) {
            return false;
        }

        $cmsModel = CmsModel::find($this->model);
        $tableName = $cmsModel->table;

        try {
            $sql = "ALTER TABLE `{$tableName}` CHANGE COLUMN `{$oldName}` `{$this->name}` {$this->define} COMMENT '{$this->title}'";
            DB::statement($sql);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete field from database table
     */
    public function deleteFieldFromTable(): bool
    {
        if (!$this->tableExists($this->model)) {
            return false;
        }

        $cmsModel = CmsModel::find($this->model);
        $tableName = $cmsModel->table;

        try {
            $sql = "ALTER TABLE `{$tableName}` DROP COLUMN `{$this->name}`";
            DB::statement($sql);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
