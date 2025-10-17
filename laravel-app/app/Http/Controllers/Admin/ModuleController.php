<?php

namespace App\Http\Controllers\Admin;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ModuleController extends AdminController
{
    /**
     * Display a listing of modules
     */
    public function index()
    {
        $modules = Module::orderBy('sort', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => $modules
        ]);
    }

    /**
     * Get available modules from directory
     */
    public function available()
    {
        $modulePath = base_path('modules');
        $available = [];

        if (File::isDirectory($modulePath)) {
            $directories = File::directories($modulePath);
            
            foreach ($directories as $dir) {
                $name = basename($dir);
                $infoFile = $dir . '/info.php';
                
                if (File::exists($infoFile)) {
                    $info = include $infoFile;
                    $installed = Module::where('name', $name)->first();
                    
                    $available[] = [
                        'name' => $name,
                        'title' => $info['title'] ?? $name,
                        'version' => $info['version'] ?? '1.0.0',
                        'author' => $info['author'] ?? 'Unknown',
                        'description' => $info['description'] ?? '',
                        'installed' => $installed ? true : false,
                        'status' => $installed ? $installed->status : -1
                    ];
                }
            }
        }

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => $available
        ]);
    }

    /**
     * Install a module
     */
    public function install(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $name = $request->input('name');
        $modulePath = base_path('modules/' . $name);
        $infoFile = $modulePath . '/info.php';

        if (!File::exists($infoFile)) {
            return response()->json([
                'code' => 0,
                'msg' => 'Module info file not found'
            ]);
        }

        // Check if already installed
        if (Module::where('name', $name)->exists()) {
            return response()->json([
                'code' => 0,
                'msg' => 'Module already installed'
            ]);
        }

        DB::beginTransaction();
        try {
            $info = include $infoFile;

            // Create module record
            $module = Module::create([
                'name' => $name,
                'title' => $info['title'] ?? $name,
                'identifier' => $info['identifier'] ?? $name,
                'version' => $info['version'] ?? '1.0.0',
                'author' => $info['author'] ?? 'Unknown',
                'config' => isset($info['config']) ? json_encode($info['config']) : null,
                'status' => 1,
                'sort' => $info['sort'] ?? 100
            ]);

            // Run install script if exists
            $installFile = $modulePath . '/install.php';
            if (File::exists($installFile)) {
                include $installFile;
            }

            // Clear cache
            Cache::forget('modules');

            DB::commit();

            action_log('module_install', 'admin_module', $module->id, auth()->id(), "安装模块：{$module->title}");

            return response()->json([
                'code' => 1,
                'msg' => 'Module installed successfully',
                'data' => $module
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 0,
                'msg' => 'Installation failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get module configuration
     */
    public function config($name)
    {
        $module = Module::where('name', $name)->firstOrFail();
        
        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => [
                'module' => $module,
                'config' => $module->config ? json_decode($module->config, true) : []
            ]
        ]);
    }

    /**
     * Save module configuration
     */
    public function saveConfig(Request $request, $name)
    {
        $module = Module::where('name', $name)->firstOrFail();

        $config = $request->input('config', []);
        $module->config = json_encode($config);
        $module->save();

        Cache::forget('modules');
        Cache::forget('module_' . $name);

        action_log('module_config', 'admin_module', $module->id, auth()->id(), "配置模块：{$module->title}");

        return response()->json([
            'code' => 1,
            'msg' => 'Configuration saved successfully'
        ]);
    }

    /**
     * Enable a module
     */
    public function enable($name)
    {
        $module = Module::where('name', $name)->firstOrFail();
        $module->status = 1;
        $module->save();

        Cache::forget('modules');

        action_log('module_enable', 'admin_module', $module->id, auth()->id(), "启用模块：{$module->title}");

        return response()->json([
            'code' => 1,
            'msg' => 'Module enabled successfully'
        ]);
    }

    /**
     * Disable a module
     */
    public function disable($name)
    {
        $module = Module::where('name', $name)->firstOrFail();
        $module->status = 0;
        $module->save();

        Cache::forget('modules');

        action_log('module_disable', 'admin_module', $module->id, auth()->id(), "禁用模块：{$module->title}");

        return response()->json([
            'code' => 1,
            'msg' => 'Module disabled successfully'
        ]);
    }

    /**
     * Uninstall a module
     */
    public function uninstall($name)
    {
        $module = Module::where('name', $name)->firstOrFail();

        DB::beginTransaction();
        try {
            $modulePath = base_path('modules/' . $name);
            
            // Run uninstall script if exists
            $uninstallFile = $modulePath . '/uninstall.php';
            if (File::exists($uninstallFile)) {
                include $uninstallFile;
            }

            $title = $module->title;
            $module->delete();

            Cache::forget('modules');
            Cache::forget('module_' . $name);

            DB::commit();

            action_log('module_uninstall', 'admin_module', 0, auth()->id(), "卸载模块：{$title}");

            return response()->json([
                'code' => 1,
                'msg' => 'Module uninstalled successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 0,
                'msg' => 'Uninstall failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update module sort order
     */
    public function sort(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'sort' => 'required|integer'
        ]);

        $module = Module::findOrFail($request->input('id'));
        $module->sort = $request->input('sort');
        $module->save();

        Cache::forget('modules');

        return response()->json([
            'code' => 1,
            'msg' => 'Sort order updated successfully'
        ]);
    }
}
