<?php

namespace App\Http\Controllers\Admin;

use App\Models\Packet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PacketController extends AdminController
{
    /**
     * Display a listing of packets
     */
    public function index()
    {
        $packets = Packet::orderBy('sort', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => $packets
        ]);
    }

    /**
     * Get available packets from directory
     */
    public function available()
    {
        $packetPath = base_path('packets');
        $available = [];

        if (File::isDirectory($packetPath)) {
            $files = File::files($packetPath);
            
            foreach ($files as $file) {
                if ($file->getExtension() === 'sql') {
                    $name = $file->getFilenameWithoutExtension();
                    $installed = Packet::where('name', $name)->first();
                    
                    // Try to get info from SQL file comments
                    $content = File::get($file->getPathname());
                    $info = $this->parsePacketInfo($content);
                    
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
     * Parse packet info from SQL comments
     */
    private function parsePacketInfo($content)
    {
        $info = [];
        
        if (preg_match('/--\s*Title:\s*(.+)/i', $content, $matches)) {
            $info['title'] = trim($matches[1]);
        }
        if (preg_match('/--\s*Version:\s*(.+)/i', $content, $matches)) {
            $info['version'] = trim($matches[1]);
        }
        if (preg_match('/--\s*Author:\s*(.+)/i', $content, $matches)) {
            $info['author'] = trim($matches[1]);
        }
        if (preg_match('/--\s*Description:\s*(.+)/i', $content, $matches)) {
            $info['description'] = trim($matches[1]);
        }
        
        return $info;
    }

    /**
     * Install a packet
     */
    public function install(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $name = $request->input('name');
        $sqlFile = base_path('packets/' . $name . '.sql');

        if (!File::exists($sqlFile)) {
            return response()->json([
                'code' => 0,
                'msg' => 'Packet SQL file not found'
            ]);
        }

        // Check if already installed
        if (Packet::where('name', $name)->exists()) {
            return response()->json([
                'code' => 0,
                'msg' => 'Packet already installed'
            ]);
        }

        DB::beginTransaction();
        try {
            $content = File::get($sqlFile);
            $info = $this->parsePacketInfo($content);

            // Replace table prefix
            $prefix = config('database.connections.mysql.prefix', '');
            $content = str_replace('dp_', $prefix, $content);

            // Execute SQL
            $statements = $this->parseSqlStatements($content);
            $tables = [];

            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    DB::statement($statement);
                    
                    // Extract table name
                    if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
                        $tables[] = $matches[1];
                    }
                }
            }

            // Create packet record
            $packet = Packet::create([
                'name' => $name,
                'title' => $info['title'] ?? $name,
                'version' => $info['version'] ?? '1.0.0',
                'author' => $info['author'] ?? 'Unknown',
                'tables' => json_encode($tables),
                'status' => 1,
                'sort' => 100
            ]);

            DB::commit();

            action_log('packet_install', 'admin_packet', $packet->id, auth()->id(), "安装数据包：{$packet->title}");

            return response()->json([
                'code' => 1,
                'msg' => 'Packet installed successfully',
                'data' => $packet
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
     * Parse SQL statements
     */
    private function parseSqlStatements($content)
    {
        // Remove comments
        $content = preg_replace('/--.*$/m', '', $content);
        $content = preg_replace('/\/\*.*?\*\//s', '', $content);
        
        // Split by semicolon
        $statements = explode(';', $content);
        
        return array_filter(array_map('trim', $statements));
    }

    /**
     * Uninstall a packet
     */
    public function uninstall($name)
    {
        $packet = Packet::where('name', $name)->firstOrFail();

        DB::beginTransaction();
        try {
            // Drop tables
            $tables = $packet->tables ? json_decode($packet->tables, true) : [];
            
            foreach ($tables as $table) {
                DB::statement("DROP TABLE IF EXISTS `{$table}`");
            }

            $title = $packet->title;
            $packet->delete();

            DB::commit();

            action_log('packet_uninstall', 'admin_packet', 0, auth()->id(), "卸载数据包：{$title}");

            return response()->json([
                'code' => 1,
                'msg' => 'Packet uninstalled successfully'
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
     * Update packet sort order
     */
    public function sort(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'sort' => 'required|integer'
        ]);

        $packet = Packet::findOrFail($request->input('id'));
        $packet->sort = $request->input('sort');
        $packet->save();

        return response()->json([
            'code' => 1,
            'msg' => 'Sort order updated successfully'
        ]);
    }
}
