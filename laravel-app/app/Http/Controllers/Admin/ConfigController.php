<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

/**
 * Config Controller - Migrated from ThinkPHP to Laravel
 * 
 * Original: app\admin\controller\Config
 * Handles system configuration management
 * 
 * @package App\Http\Controllers\Admin
 */
class ConfigController extends AdminController
{
    /**
     * Display configuration list
     *
     * @param Request $request
     * @param string $group Configuration group
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $group = 'base')
    {
        // Get configuration groups
        $groups = DB::table('admin_config')
            ->distinct()
            ->pluck('title', 'group');

        $tabList = [];
        foreach ($groups as $key => $value) {
            $tabList[] = [
                'title' => $value,
                'url' => route('admin.config.index', ['group' => $key])
            ];
        }

        // Get configurations for the group
        $configs = DB::table('admin_config')
            ->where('group', $group)
            ->where('status', 1)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        return view('admin.config.index', [
            'configs' => $configs,
            'group' => $group,
            'tabNav' => ['tab_list' => $tabList, 'curr_tab' => $group],
            'pageTitle' => '系统配置'
        ]);
    }

    /**
     * Update configuration
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $configs = $request->except('_token', '_method');

        foreach ($configs as $name => $value) {
            // Handle array values
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            DB::table('admin_config')
                ->where('name', $name)
                ->update(['value' => $value]);
        }

        // Clear configuration cache
        Cache::forget('system_config');

        action_log('config_update', 'admin_config', 0, $this->getUserId(), '更新系统配置');

        return $this->success('保存成功');
    }

    /**
     * Show form for creating a new configuration
     *
     * @param string $group Configuration group
     * @return \Illuminate\View\View
     */
    public function create($group = 'base')
    {
        return view('admin.config.create', [
            'group' => $group,
            'pageTitle' => '新增配置'
        ]);
    }

    /**
     * Store a newly created configuration
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:32',
            'name' => 'required|string|max:32|unique:admin_config,name',
            'group' => 'required|string|max:16',
            'type' => 'required|string|max:16',
        ], [
            'title.required' => '配置标题不能为空',
            'name.required' => '配置名称不能为空',
            'name.unique' => '配置名称已存在',
            'group.required' => '配置分组不能为空',
            'type.required' => '配置类型不能为空',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $data = $request->only([
            'title', 'name', 'group', 'type', 'value', 
            'options', 'tip', 'status', 'sort'
        ]);

        $data['status'] = $data['status'] ?? 1;
        $data['sort'] = $data['sort'] ?? 100;

        $id = DB::table('admin_config')->insertGetId($data);

        // Clear configuration cache
        Cache::forget('system_config');

        action_log('config_add', 'admin_config', $id, $this->getUserId(), $data['title']);

        return $this->success('新增成功', ['id' => $id]);
    }

    /**
     * Show form for editing a configuration
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $config = DB::table('admin_config')->where('id', $id)->first();

        if (!$config) {
            abort(404, '配置不存在');
        }

        return view('admin.config.edit', [
            'config' => $config,
            'pageTitle' => '编辑配置'
        ]);
    }

    /**
     * Update the specified configuration
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateConfig(Request $request, $id)
    {
        $config = DB::table('admin_config')->where('id', $id)->first();

        if (!$config) {
            return $this->error('配置不存在');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:32',
            'name' => 'required|string|max:32|unique:admin_config,name,' . $id,
            'group' => 'required|string|max:16',
            'type' => 'required|string|max:16',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $data = $request->only([
            'title', 'name', 'group', 'type', 'value', 
            'options', 'tip', 'status', 'sort'
        ]);

        DB::table('admin_config')->where('id', $id)->update($data);

        // Clear configuration cache
        Cache::forget('system_config');

        action_log('config_edit', 'admin_config', $id, $this->getUserId(), $config->title);

        return $this->success('编辑成功');
    }

    /**
     * Remove the specified configuration
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $config = DB::table('admin_config')->where('id', $id)->first();

        if (!$config) {
            return $this->error('配置不存在');
        }

        DB::table('admin_config')->where('id', $id)->delete();

        // Clear configuration cache
        Cache::forget('system_config');

        action_log('config_delete', 'admin_config', $id, $this->getUserId(), $config->title);

        return $this->success('删除成功');
    }

    /**
     * Quick edit configuration value
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickEdit(Request $request)
    {
        $id = $request->input('id');
        $field = $request->input('field');
        $value = $request->input('value');

        if (!$id || !$field) {
            return $this->error('参数错误');
        }

        DB::table('admin_config')->where('id', $id)->update([$field => $value]);

        // Clear configuration cache
        Cache::forget('system_config');

        return $this->success('更新成功');
    }

    /**
     * Enable/disable configuration
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request)
    {
        $ids = $request->input('ids', []);
        $status = $request->input('status', 1);

        if (empty($ids)) {
            return $this->error('请选择要操作的配置');
        }

        DB::table('admin_config')->whereIn('id', $ids)->update(['status' => $status]);

        // Clear configuration cache
        Cache::forget('system_config');

        return $this->success('操作成功');
    }
}
