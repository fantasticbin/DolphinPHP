<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? '控制台' }} - DolphinPHP 管理后台</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-name {
            font-size: 14px;
        }
        
        .btn {
            padding: 8px 16px;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .breadcrumb {
            font-size: 14px;
            color: #666;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .stat-icon {
            font-size: 32px;
            margin-bottom: 15px;
        }
        
        .stat-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 15px;
            font-weight: 500;
            color: #333;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success {
            background: #10b981;
            color: white;
        }
        
        .badge-warning {
            background: #f59e0b;
            color: white;
        }
        
        .badge-info {
            background: #3b82f6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                🐬 DolphinPHP
            </div>
            <div class="user-info">
                <span class="user-name">{{ auth()->user()->nickname ?? auth()->user()->username }}</span>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn">退出</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">{{ $pageTitle ?? '控制台' }}</h1>
            <div class="breadcrumb">首页 / {{ $pageTitle ?? '控制台' }}</div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-title">用户总数</div>
                <div class="stat-value">{{ \App\Models\User::count() }}</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🔐</div>
                <div class="stat-title">角色数量</div>
                <div class="stat-value">{{ \App\Models\Role::count() }}</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-title">菜单数量</div>
                <div class="stat-value">{{ \App\Models\Menu::count() }}</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⚙️</div>
                <div class="stat-title">配置项</div>
                <div class="stat-value">{{ \App\Models\Config::count() }}</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-title">系统信息</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">框架版本</div>
                    <div class="info-value">
                        <span class="badge badge-success">Laravel {{ app()->version() }}</span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">PHP 版本</div>
                    <div class="info-value">
                        <span class="badge badge-info">PHP {{ PHP_VERSION }}</span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">运行环境</div>
                    <div class="info-value">{{ config('app.env') }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">调试模式</div>
                    <div class="info-value">
                        <span class="badge {{ config('app.debug') ? 'badge-warning' : 'badge-success' }}">
                            {{ config('app.debug') ? '已开启' : '已关闭' }}
                        </span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">时区</div>
                    <div class="info-value">{{ config('app.timezone') }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">语言</div>
                    <div class="info-value">{{ config('app.locale') }}</div>
                </div>
            </div>
        </div>
        
        @if($defaultPass ?? false)
        <div class="card" style="border-left: 4px solid #f59e0b; background: #fffbeb;">
            <div class="card-title" style="color: #f59e0b; border-color: #f59e0b;">
                ⚠️ 安全提醒
            </div>
            <p style="color: #92400e; margin-bottom: 15px;">
                检测到管理员账号仍在使用默认密码，为了系统安全，请尽快修改密码！
            </p>
            <a href="{{ route('admin.profile') }}" class="btn" style="background: #f59e0b; border-color: #f59e0b; color: white; display: inline-block;">
                立即修改
            </a>
        </div>
        @endif
        
        <div class="card">
            <div class="card-title">快速链接</div>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="{{ route('admin.menu.index') }}" class="btn" style="color: #667eea; border-color: #667eea;">
                    📋 菜单管理
                </a>
                <a href="{{ route('admin.config.index') }}" class="btn" style="color: #667eea; border-color: #667eea;">
                    ⚙️ 系统配置
                </a>
                <a href="{{ route('admin.profile') }}" class="btn" style="color: #667eea; border-color: #667eea;">
                    👤 个人资料
                </a>
                <form method="POST" action="{{ route('admin.wipe-cache') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn" style="color: #667eea; border-color: #667eea;">
                        🗑️ 清除缓存
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
