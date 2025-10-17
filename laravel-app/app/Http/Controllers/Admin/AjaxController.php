<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ajax Controller
 * Handles AJAX requests for dynamic data
 */
class AjaxController extends AdminController
{
    /**
     * Get cascading/level data
     * Used for dependent dropdown selections
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLevelData(Request $request)
    {
        $token = $request->get('token', '');
        $pid = $request->get('pid', 0);
        $pidkey = $request->get('pidkey', 'pid');

        if ($token == '') {
            return $this->error('缺少Token');
        }

        // Get token data from session
        $tokenData = session($token);

        if (!$tokenData) {
            return $this->error('Token无效或已过期');
        }

        $table = $tokenData['table'] ?? '';
        $option = $tokenData['option'] ?? 'name';
        $key = $tokenData['key'] ?? 'id';

        if (!$table) {
            return $this->error('缺少表名');
        }

        try {
            // Query data
            $dataList = DB::table($table)
                ->where($pidkey, $pid)
                ->pluck($option, $key)
                ->toArray();

            if (empty($dataList)) {
                return $this->success('暂无数据', ['list' => []]);
            }

            return $this->success('获取成功', ['list' => $dataList]);
        } catch (\Exception $e) {
            return $this->error('查询失败：' . $e->getMessage());
        }
    }

    /**
     * Get database table list
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTable(Request $request)
    {
        $keyword = $request->get('keyword', '');

        try {
            // Get database name
            $database = config('database.connections.' . config('database.default') . '.database');

            // Query tables
            $query = "SELECT TABLE_NAME as name, TABLE_COMMENT as comment 
                     FROM information_schema.TABLES 
                     WHERE TABLE_SCHEMA = ?";

            $params = [$database];

            if ($keyword) {
                $query .= " AND (TABLE_NAME LIKE ? OR TABLE_COMMENT LIKE ?)";
                $params[] = '%' . $keyword . '%';
                $params[] = '%' . $keyword . '%';
            }

            $tables = DB::select($query, $params);

            $result = [];
            foreach ($tables as $table) {
                $result[] = [
                    'name' => $table->name,
                    'comment' => $table->comment ?: $table->name,
                ];
            }

            return $this->success('获取成功', ['list' => $result]);
        } catch (\Exception $e) {
            return $this->error('获取失败：' . $e->getMessage());
        }
    }

    /**
     * Get table field information
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTableInfo(Request $request)
    {
        $table = $request->get('table', '');

        if (!$table) {
            return $this->error('缺少表名');
        }

        try {
            // Get table prefix
            $prefix = config('database.connections.' . config('database.default') . '.prefix', '');

            // Remove prefix if exists
            $tableName = $table;
            if ($prefix && strpos($table, $prefix) === 0) {
                $tableName = substr($table, strlen($prefix));
            }

            // Check if table exists
            if (!Schema::hasTable($tableName)) {
                return $this->error('表不存在');
            }

            // Get columns
            $columns = DB::select("SHOW FULL COLUMNS FROM " . $table);

            $fields = [];
            foreach ($columns as $column) {
                $fields[] = [
                    'name' => $column->Field,
                    'type' => $column->Type,
                    'null' => $column->Null === 'YES',
                    'key' => $column->Key,
                    'default' => $column->Default,
                    'extra' => $column->Extra,
                    'comment' => $column->Comment ?: $column->Field,
                ];
            }

            return $this->success('获取成功', ['fields' => $fields]);
        } catch (\Exception $e) {
            return $this->error('获取失败：' . $e->getMessage());
        }
    }

    /**
     * Get menu tree for select
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenuTree(Request $request)
    {
        $type = $request->get('type', 'admin');

        try {
            $query = DB::table('admin_menu')->where('status', 1);

            if ($type === 'admin') {
                $query->where('module', 'admin');
            }

            $menus = $query->orderBy('sort', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->toArray();

            // Build tree
            $tree = $this->buildMenuTree($menus);

            return $this->success('获取成功', ['tree' => $tree]);
        } catch (\Exception $e) {
            return $this->error('获取失败：' . $e->getMessage());
        }
    }

    /**
     * Build menu tree
     *
     * @param array $menus
     * @param int $pid
     * @return array
     */
    private function buildMenuTree($menus, $pid = 0)
    {
        $tree = [];

        foreach ($menus as $menu) {
            if ($menu->pid == $pid) {
                $menu->children = $this->buildMenuTree($menus, $menu->id);
                $tree[] = $menu;
            }
        }

        return $tree;
    }

    /**
     * Clear cache
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache(Request $request)
    {
        $type = $request->get('type', 'all');

        try {
            switch ($type) {
                case 'config':
                    cache()->forget('system_config');
                    break;
                case 'menu':
                    cache()->forget('admin_menu');
                    break;
                case 'all':
                default:
                    cache()->flush();
                    break;
            }

            return $this->success('缓存清理成功');
        } catch (\Exception $e) {
            return $this->error('缓存清理失败：' . $e->getMessage());
        }
    }
}
