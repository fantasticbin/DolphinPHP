<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DatabaseController extends AdminController
{
    /**
     * Get database tables
     */
    public function tables()
    {
        $database = config('database.connections.mysql.database');
        
        $tables = DB::select("
            SELECT 
                table_name as name,
                table_comment as comment,
                table_rows as rows,
                ROUND((data_length + index_length) / 1024 / 1024, 2) as size
            FROM information_schema.tables
            WHERE table_schema = ?
            ORDER BY table_name
        ", [$database]);

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => $tables
        ]);
    }

    /**
     * Get table structure
     */
    public function tableInfo($table)
    {
        $columns = DB::select("SHOW FULL COLUMNS FROM `{$table}`");
        $indexes = DB::select("SHOW INDEX FROM `{$table}`");

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => [
                'columns' => $columns,
                'indexes' => $indexes
            ]
        ]);
    }

    /**
     * Optimize table
     */
    public function optimize(Request $request)
    {
        $request->validate([
            'tables' => 'required|array'
        ]);

        $tables = $request->input('tables');
        
        foreach ($tables as $table) {
            DB::statement("OPTIMIZE TABLE `{$table}`");
        }

        action_log('database_optimize', 'admin_database', 0, auth()->id(), "优化数据表：" . implode(',', $tables));

        return response()->json([
            'code' => 1,
            'msg' => 'Tables optimized successfully'
        ]);
    }

    /**
     * Repair table
     */
    public function repair(Request $request)
    {
        $request->validate([
            'tables' => 'required|array'
        ]);

        $tables = $request->input('tables');
        
        foreach ($tables as $table) {
            DB::statement("REPAIR TABLE `{$table}`");
        }

        action_log('database_repair', 'admin_database', 0, auth()->id(), "修复数据表：" . implode(',', $tables));

        return response()->json([
            'code' => 1,
            'msg' => 'Tables repaired successfully'
        ]);
    }

    /**
     * Backup database
     */
    public function backup(Request $request)
    {
        $request->validate([
            'tables' => 'array'
        ]);

        $database = config('database.connections.mysql.database');
        $tables = $request->input('tables', []);

        // If no tables specified, backup all
        if (empty($tables)) {
            $result = DB::select("SHOW TABLES");
            $key = "Tables_in_{$database}";
            $tables = array_map(function($item) use ($key) {
                return $item->$key;
            }, $result);
        }

        try {
            $filename = 'backup_' . date('YmdHis') . '.sql';
            $filepath = storage_path('app/backups/' . $filename);

            // Ensure directory exists
            File::ensureDirectoryExists(dirname($filepath));

            $content = "-- DolphinPHP Database Backup\n";
            $content .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
            $content .= "-- Database: {$database}\n\n";

            foreach ($tables as $table) {
                // Get table structure
                $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
                $content .= "\n\n-- Table structure for `{$table}`\n";
                $content .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $content .= $createTable[0]->{'Create Table'} . ";\n\n";

                // Get table data
                $rows = DB::select("SELECT * FROM `{$table}`");
                
                if (!empty($rows)) {
                    $content .= "-- Data for `{$table}`\n";
                    
                    foreach ($rows as $row) {
                        $values = array_map(function($value) {
                            return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                        }, (array)$row);
                        
                        $content .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                }
            }

            File::put($filepath, $content);

            action_log('database_backup', 'admin_database', 0, auth()->id(), "备份数据库：{$filename}");

            return response()->json([
                'code' => 1,
                'msg' => 'Database backed up successfully',
                'data' => [
                    'filename' => $filename,
                    'size' => File::size($filepath),
                    'path' => 'backups/' . $filename
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 0,
                'msg' => 'Backup failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * List backups
     */
    public function backups()
    {
        $backupPath = storage_path('app/backups');
        
        if (!File::isDirectory($backupPath)) {
            return response()->json([
                'code' => 1,
                'msg' => 'Success',
                'data' => []
            ]);
        }

        $files = File::files($backupPath);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'time' => $file->getMTime(),
                    'path' => 'backups/' . $file->getFilename()
                ];
            }
        }

        // Sort by time descending
        usort($backups, function($a, $b) {
            return $b['time'] - $a['time'];
        });

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => $backups
        ]);
    }

    /**
     * Restore database from backup
     */
    public function restore(Request $request)
    {
        $request->validate([
            'filename' => 'required|string'
        ]);

        $filename = $request->input('filename');
        $filepath = storage_path('app/backups/' . $filename);

        if (!File::exists($filepath)) {
            return response()->json([
                'code' => 0,
                'msg' => 'Backup file not found'
            ]);
        }

        DB::beginTransaction();
        try {
            $content = File::get($filepath);
            
            // Remove comments
            $content = preg_replace('/--.*$/m', '', $content);
            
            // Split by semicolon
            $statements = explode(';', $content);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    DB::statement($statement);
                }
            }

            DB::commit();

            action_log('database_restore', 'admin_database', 0, auth()->id(), "恢复数据库：{$filename}");

            return response()->json([
                'code' => 1,
                'msg' => 'Database restored successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 0,
                'msg' => 'Restore failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete backup file
     */
    public function deleteBackup(Request $request)
    {
        $request->validate([
            'filename' => 'required|string'
        ]);

        $filename = $request->input('filename');
        $filepath = storage_path('app/backups/' . $filename);

        if (!File::exists($filepath)) {
            return response()->json([
                'code' => 0,
                'msg' => 'Backup file not found'
            ]);
        }

        File::delete($filepath);

        action_log('database_delete_backup', 'admin_database', 0, auth()->id(), "删除备份：{$filename}");

        return response()->json([
            'code' => 1,
            'msg' => 'Backup deleted successfully'
        ]);
    }

    /**
     * Download backup file
     */
    public function download($filename)
    {
        $filepath = storage_path('app/backups/' . $filename);

        if (!File::exists($filepath)) {
            return response()->json([
                'code' => 0,
                'msg' => 'Backup file not found'
            ]);
        }

        return response()->download($filepath);
    }
}
