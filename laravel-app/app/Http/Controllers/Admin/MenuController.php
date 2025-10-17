<?php

namespace App\Http\Controllers\Admin;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

/**
 * Menu Controller - Migrated from ThinkPHP to Laravel
 * 
 * Original: app\admin\controller\Menu
 * Handles menu management and hierarchy
 * 
 * @package App\Http\Controllers\Admin
 */
class MenuController extends AdminController
{
    /**
     * Display menu list
     *
     * @param Request $request
     * @param string $group Menu group
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $group = 'admin')
    {
        // Handle menu sorting via POST
        if ($request->isMethod('post')) {
            $modules = $request->input('sort', []);
            if (!empty($modules)) {
                $data = [];
                foreach ($modules as $key => $moduleId) {
                    $data[] = [
                        'id' => $moduleId,
                        'sort' => $key + 1
                    ];
                }
                
                foreach ($data as $item) {
                    Menu::where('id', $item['id'])->update(['sort' => $item['sort']]);
                }
                
                return $this->success('保存成功');
            }
        }

        // Get menu groups
        $groups = Menu::topLevel()->active()->distinct()->pluck('title', 'module');
        
        $tabList = [];
        foreach ($groups as $key => $value) {
            $tabList[] = [
                'title' => $value,
                'url' => route('admin.menu.index', ['group' => $key])
            ];
        }

        // Handle module sorting
        if ($group == 'module-sort') {
            $modules = Menu::topLevel()
                ->active()
                ->ordered()
                ->get(['id', 'icon', 'title']);
            
            return view('admin.menu.sort', [
                'modules' => $modules,
                'tabNav' => ['tab_list' => $tabList, 'curr_tab' => $group],
                'pageTitle' => '节点管理'
            ]);
        }

        // Get menu data for the group
        $dataList = Menu::byModule($group)->ordered()->get();
        $menus = $this->buildMenuTree($dataList);

        return view('admin.menu.index', [
            'menus' => $menus,
            'tabNav' => ['tab_list' => $tabList, 'curr_tab' => $group],
            'pageTitle' => '节点管理'
        ]);
    }

    /**
     * Show form for creating a new menu
     *
     * @param string $module Module name
     * @param int $pid Parent menu ID
     * @return \Illuminate\View\View
     */
    public function create($module = 'admin', $pid = 0)
    {
        $parentMenu = $pid ? Menu::find($pid) : null;
        
        return view('admin.menu.create', [
            'module' => $module,
            'pid' => $pid,
            'parentMenu' => $parentMenu,
            'pageTitle' => '新增节点'
        ]);
    }

    /**
     * Store a newly created menu
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:32',
            'module' => 'required|string|max:16',
            'pid' => 'required|integer',
            'url_value' => 'nullable|string|max:255',
            'url_type' => 'nullable|string|max:16',
            'icon' => 'nullable|string|max:64',
        ], [
            'title.required' => '节点标题不能为空',
            'module.required' => '所属模块不能为空',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $data = $request->only([
            'pid', 'module', 'title', 'icon', 'url_value', 
            'url_type', 'url_target', 'online_hide', 'status', 'sort', 'params'
        ]);

        // Convert URL to lowercase
        if (isset($data['url_value'])) {
            $data['url_value'] = strtolower($data['url_value']);
        }

        // Set default values
        $data['status'] = $data['status'] ?? 1;
        $data['online_hide'] = $data['online_hide'] ?? 0;
        $data['url_target'] = $data['url_target'] ?? '_self';

        $menu = Menu::create($data);

        // Clear menu cache
        $this->clearMenuCache();

        action_log('menu_add', 'admin_menu', $menu->id, $this->getUserId(), $menu->title);

        return $this->success('新增成功', ['id' => $menu->id]);
    }

    /**
     * Show form for editing a menu
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $menu = Menu::findOrFail($id);
        $parentMenu = $menu->pid ? Menu::find($menu->pid) : null;

        return view('admin.menu.edit', [
            'menu' => $menu,
            'parentMenu' => $parentMenu,
            'pageTitle' => '编辑节点'
        ]);
    }

    /**
     * Update the specified menu
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:32',
            'module' => 'required|string|max:16',
            'pid' => 'required|integer',
            'url_value' => 'nullable|string|max:255',
            'url_type' => 'nullable|string|max:16',
            'icon' => 'nullable|string|max:64',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        // Check for circular reference
        if ($request->pid == $id) {
            return $this->error('上级节点不能是自己');
        }

        $childIds = Menu::getChildIds($id);
        if (in_array($request->pid, $childIds)) {
            return $this->error('上级节点不能是自己的子节点');
        }

        $data = $request->only([
            'pid', 'module', 'title', 'icon', 'url_value', 
            'url_type', 'url_target', 'online_hide', 'status', 'sort', 'params'
        ]);

        // Convert URL to lowercase
        if (isset($data['url_value'])) {
            $data['url_value'] = strtolower($data['url_value']);
        }

        $menu->update($data);

        // Update module for all children if module changed
        if ($request->module != $menu->module) {
            $this->updateChildrenModule($id, $request->module);
        }

        // Clear menu cache
        $this->clearMenuCache();

        action_log('menu_edit', 'admin_menu', $id, $this->getUserId(), $menu->title);

        return $this->success('编辑成功');
    }

    /**
     * Remove the specified menu
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);

        // Check if menu has children
        if (Menu::where('pid', $id)->exists()) {
            return $this->error('该节点有子节点，无法删除');
        }

        $title = $menu->title;
        $menu->delete();

        // Clear menu cache
        $this->clearMenuCache();

        action_log('menu_delete', 'admin_menu', $id, $this->getUserId(), $title);

        return $this->success('删除成功');
    }

    /**
     * Enable/disable menu
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request)
    {
        $ids = $request->input('ids', []);
        $status = $request->input('status', 1);

        if (empty($ids)) {
            return $this->error('请选择要操作的节点');
        }

        Menu::whereIn('id', $ids)->update(['status' => $status]);

        // Clear menu cache
        $this->clearMenuCache();

        return $this->success('操作成功');
    }

    /**
     * Build menu tree structure
     *
     * @param \Illuminate\Support\Collection $menus
     * @param int $pid
     * @param int $level
     * @return array
     */
    protected function buildMenuTree($menus, $pid = 0, $level = 0)
    {
        $tree = [];
        
        foreach ($menus as $menu) {
            if ($menu->pid == $pid) {
                $menu->level = $level;
                $menu->children = $this->buildMenuTree($menus, $menu->id, $level + 1);
                $tree[] = $menu;
            }
        }
        
        return $tree;
    }

    /**
     * Update module for all children recursively
     *
     * @param int $id
     * @param string $module
     * @return void
     */
    protected function updateChildrenModule($id, $module)
    {
        $children = Menu::where('pid', $id)->get();
        
        foreach ($children as $child) {
            $child->update(['module' => $module]);
            $this->updateChildrenModule($child->id, $module);
        }
    }

    /**
     * Clear menu cache
     *
     * @return void
     */
    protected function clearMenuCache()
    {
        Cache::tags(['menu'])->flush();
    }
}
