<?php

namespace App\Http\Controllers\Admin;

use App\Models\Icon;
use App\Models\IconList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

/**
 * Icon Controller
 * Manages icon libraries and icon lists
 */
class IconController extends AdminController
{
    /**
     * Display icon library listing
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Icon::query();

        // Search by name
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $icons = $query->orderBy('sort', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 15));

        return $this->success('获取成功', [
            'data' => $icons->items(),
            'total' => $icons->total(),
            'per_page' => $icons->perPage(),
            'current_page' => $icons->currentPage(),
        ]);
    }

    /**
     * Store new icon library
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'url' => 'required|url|max:255',
            'type' => 'required|in:font,image',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $icon = Icon::create([
                'name' => $request->name,
                'url' => $request->url,
                'type' => $request->type,
                'font_class' => $request->get('font_class', ''),
                'sort' => $request->get('sort', 100),
                'status' => $request->get('status', 1),
            ]);

            // Clear cache
            Cache::forget('icon_libraries');

            return $this->success('添加成功', ['id' => $icon->id]);
        } catch (\Exception $e) {
            return $this->error('添加失败：' . $e->getMessage());
        }
    }

    /**
     * Update icon library
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $icon = Icon::find($id);

        if (!$icon) {
            return $this->error('图标库不存在');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:50',
            'url' => 'url|max:255',
            'type' => 'in:font,image',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $icon->update($request->only(['name', 'url', 'type', 'font_class', 'sort', 'status']));

            // Clear cache
            Cache::forget('icon_libraries');

            return $this->success('更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * Delete icon library
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $icon = Icon::find($id);

        if (!$icon) {
            return $this->error('图标库不存在');
        }

        try {
            // Delete all icon items
            IconList::where('icon_id', $id)->delete();

            // Delete library
            $icon->delete();

            // Clear cache
            Cache::forget('icon_libraries');

            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * Get icon list for a library
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function items(Request $request, $id)
    {
        $icon = Icon::find($id);

        if (!$icon) {
            return $this->error('图标库不存在');
        }

        $query = IconList::where('icon_id', $id);

        // Search by title or class
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('class', 'like', '%' . $keyword . '%');
            });
        }

        $items = $query->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($request->get('per_page', 50));

        return $this->success('获取成功', [
            'library' => $icon,
            'data' => $items->items(),
            'total' => $items->total(),
            'per_page' => $items->perPage(),
            'current_page' => $items->currentPage(),
        ]);
    }

    /**
     * Reload/update icon library from URL
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reload($id)
    {
        $icon = Icon::find($id);

        if (!$icon) {
            return $this->error('图标库不存在');
        }

        try {
            // Fetch CSS content from URL
            $content = @file_get_contents($icon->url);

            if ($content === false) {
                return $this->error('无法获取图标库内容');
            }

            // Parse CSS for icon classes (basic implementation)
            preg_match_all('/\.([\w-]+):before\s*\{[^}]*content:\s*["\']\\\\([^"\']+)["\']/', $content, $matches);

            if (empty($matches[1])) {
                return $this->error('未能解析到图标信息');
            }

            // Delete existing items
            IconList::where('icon_id', $id)->delete();

            // Insert new items
            $sort = 0;
            foreach ($matches[1] as $key => $class) {
                IconList::create([
                    'icon_id' => $id,
                    'title' => $class,
                    'class' => $class,
                    'code' => isset($matches[2][$key]) ? $matches[2][$key] : '',
                    'sort' => $sort++,
                ]);
            }

            // Clear cache
            Cache::forget('icon_library_' . $id);

            return $this->success('更新成功，共导入 ' . count($matches[1]) . ' 个图标');
        } catch (\Exception $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * Update icon library status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            Icon::whereIn('id', $request->ids)->update(['status' => $request->status]);

            // Clear cache
            Cache::forget('icon_libraries');

            return $this->success('状态更新成功');
        } catch (\Exception $e) {
            return $this->error('状态更新失败：' . $e->getMessage());
        }
    }
}
