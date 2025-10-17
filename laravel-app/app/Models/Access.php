<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

/**
 * User Authorization Model
 * Manages user authorization nodes
 */
class Access extends Model
{
    protected $table = 'admin_access';
    
    public $timestamps = false;
    
    protected $fillable = [
        'uid',
        'module',
        'group',
        'nid',
    ];
    
    /**
     * Get user authorization nodes
     *
     * @param int $uid User ID
     * @param string $group Permission group (can use dot notation like "user.group")
     * @return array|false
     */
    public function getAuthNode($uid = 0, $group = '')
    {
        if ($uid == 0 || $group == '') {
            return false;
        }
        
        if (strpos($group, '.')) {
            list($module, $group) = explode('.', $group, 2);
        } else {
            $module = Request::segment(1, 'admin');
        }
        
        return $this->where([
            'module' => $module,
            'group'  => $group,
            'uid'    => $uid
        ])->pluck('nid')->toArray();
    }
    
    /**
     * Check if user has authorization for a specific node
     *
     * @param int $uid User ID
     * @param string $group Permission group
     * @param int $node Node ID to check
     * @return bool
     */
    public function checkAuthNode($uid = 0, $group = '', $node = 0)
    {
        if ($uid == 0 || $group == '' || $node == 0) {
            return false;
        }
        
        // Get all authorized nodes for the user
        $nodes = $this->getAuthNode($uid, $group);
        if (!$nodes) {
            return false;
        }
        
        return in_array($node, $nodes);
    }
    
    /**
     * Batch add authorization nodes for user
     *
     * @param int $uid User ID
     * @param string $module Module name
     * @param string $group Group name
     * @param array $nodes Array of node IDs
     * @return bool
     */
    public static function addAuthNodes($uid, $module, $group, $nodes)
    {
        if (empty($nodes) || !is_array($nodes)) {
            return false;
        }
        
        // Delete existing
        self::where([
            'uid' => $uid,
            'module' => $module,
            'group' => $group
        ])->delete();
        
        // Insert new
        $data = [];
        foreach ($nodes as $nid) {
            $data[] = [
                'uid' => $uid,
                'module' => $module,
                'group' => $group,
                'nid' => $nid
            ];
        }
        
        return self::insert($data);
    }
}
