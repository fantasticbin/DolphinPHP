<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Attachment Controller
 * Handles file upload and attachment management
 */
class AttachmentController extends AdminController
{
    /**
     * Display attachment listing
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Attachment::query();

        // Search by name
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filter by driver
        if ($request->has('driver')) {
            $query->where('driver', $request->driver);
        }

        // Filter by user
        if ($request->has('uid')) {
            $query->where('uid', $request->uid);
        }

        $attachments = $query->orderBy('sort', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 15));

        // Format response
        $data = $attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'name' => $attachment->name,
                'path' => $attachment->getFullPath(),
                'thumb' => $attachment->getThumbPath(),
                'size' => $attachment->getFormattedSize(),
                'ext' => $attachment->ext,
                'mime' => $attachment->mime,
                'driver' => $attachment->driver,
                'is_image' => in_array(strtolower($attachment->ext), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']),
                'created_at' => $attachment->create_time,
            ];
        });

        return $this->success('获取成功', [
            'data' => $data,
            'total' => $attachments->total(),
            'per_page' => $attachments->perPage(),
            'current_page' => $attachments->currentPage(),
        ]);
    }

    /**
     * Upload file
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $file = $request->file('file');
        $driver = $request->get('driver', 'local');

        try {
            // Store file
            if ($driver === 'local') {
                $path = $file->store('uploads/' . date('Ymd'), 'public');
            } else {
                $path = $file->store('uploads/' . date('Ymd'));
            }

            // Get file info
            $fileInfo = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'ext' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'md5' => md5_file($file->getRealPath()),
                'sha1' => sha1_file($file->getRealPath()),
                'driver' => $driver,
                'uid' => auth()->id() ?? 0,
            ];

            // Create thumbnail for images
            if (in_array(strtolower($fileInfo['ext']), ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                // Get image dimensions
                $imageSize = getimagesize($file->getRealPath());
                if ($imageSize) {
                    $fileInfo['width'] = $imageSize[0];
                    $fileInfo['height'] = $imageSize[1];
                }
            }

            // Save to database
            $attachment = Attachment::create($fileInfo);

            return $this->success('上传成功', [
                'id' => $attachment->id,
                'name' => $attachment->name,
                'path' => $attachment->getFullPath(),
                'url' => $attachment->getFullPath(),
            ]);
        } catch (\Exception $e) {
            return $this->error('上传失败：' . $e->getMessage());
        }
    }

    /**
     * Delete attachment
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            return $this->error('附件不存在');
        }

        try {
            // Delete physical file
            if ($attachment->driver === 'local') {
                Storage::disk('public')->delete($attachment->path);
                if ($attachment->thumb) {
                    Storage::disk('public')->delete($attachment->thumb);
                }
            }

            // Delete database record
            $attachment->delete();

            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * Get attachment info
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            return $this->error('附件不存在');
        }

        return $this->success('获取成功', [
            'id' => $attachment->id,
            'name' => $attachment->name,
            'path' => $attachment->getFullPath(),
            'thumb' => $attachment->getThumbPath(),
            'size' => $attachment->getFormattedSize(),
            'ext' => $attachment->ext,
            'mime' => $attachment->mime,
            'driver' => $attachment->driver,
            'width' => $attachment->width,
            'height' => $attachment->height,
            'md5' => $attachment->md5,
            'sha1' => $attachment->sha1,
            'created_at' => $attachment->create_time,
        ]);
    }
}
