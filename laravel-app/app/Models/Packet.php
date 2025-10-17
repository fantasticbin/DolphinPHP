<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Data Packet Model
 * Manages SQL data packets for installation
 */
class Packet extends Model
{
    protected $table = 'admin_packet';
    
    protected $fillable = [
        'name',
        'title',
        'description',
        'author',
        'version',
        'tables',
        'status',
    ];
    
    protected $casts = [
        'tables' => 'array',
    ];
    
    /**
     * Get packet info from file
     *
     * @param string $name Packet name
     * @return array
     */
    public static function getInfoFromFile($name = '')
    {
        if (empty($name)) {
            return [];
        }
        
        $packetPath = config('dolphin.packet_path', storage_path('packets/'));
        $infoFile = $packetPath . $name . '/info.php';
        
        if (File::exists($infoFile)) {
            return include $infoFile;
        }
        
        return [];
    }
    
    /**
     * Install data packet
     *
     * @param string $name Packet name
     * @return bool|string True on success, error message on failure
     */
    public static function install($name = '')
    {
        $info = self::getInfoFromFile($name);
        
        if (empty($info) || !isset($info['tables'])) {
            return 'Packet info not found or invalid';
        }
        
        $packetPath = config('dolphin.packet_path', storage_path('packets/'));
        $prefix = config('database.connections.mysql.prefix', '');
        
        foreach ($info['tables'] as $table) {
            $sqlFile = $packetPath . $name . "/{$table}.sql";
            
            if (!File::exists($sqlFile)) {
                return "SQL file [{$table}.sql] not found";
            }
            
            $sql = File::get($sqlFile);
            
            // Replace database prefix if specified
            if (isset($info['database_prefix']) && !empty($info['database_prefix'])) {
                $sql = str_replace($info['database_prefix'], $prefix, $sql);
            }
            
            // Split SQL statements
            $statements = self::splitSql($sql);
            
            try {
                foreach ($statements as $statement) {
                    if (!empty(trim($statement))) {
                        DB::statement($statement);
                    }
                }
            } catch (\Exception $e) {
                return "Error executing SQL: " . $e->getMessage();
            }
        }
        
        return true;
    }
    
    /**
     * Uninstall data packet
     *
     * @param string $name Packet name
     * @return bool|string
     */
    public static function uninstall($name = '')
    {
        $info = self::getInfoFromFile($name);
        
        if (empty($info) || !isset($info['tables'])) {
            return 'Packet info not found or invalid';
        }
        
        $prefix = config('database.connections.mysql.prefix', '');
        
        try {
            foreach ($info['tables'] as $table) {
                DB::statement("DROP TABLE IF EXISTS `{$prefix}{$table}`");
            }
            
            self::where('name', $name)->delete();
        } catch (\Exception $e) {
            return "Error uninstalling packet: " . $e->getMessage();
        }
        
        return true;
    }
    
    /**
     * Split SQL file into individual statements
     *
     * @param string $sql SQL content
     * @return array
     */
    private static function splitSql($sql)
    {
        // Remove comments
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split by semicolon
        $statements = explode(';', $sql);
        
        return array_filter(array_map('trim', $statements));
    }
}
