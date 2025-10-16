<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DolphinPHP - Laravel 11</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 60px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 600px;
        }
        h1 {
            color: #667eea;
            font-size: 3em;
            margin: 0 0 20px 0;
        }
        p {
            color: #666;
            font-size: 1.2em;
            line-height: 1.6;
        }
        .badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            margin: 10px 5px;
        }
        .success {
            color: #10b981;
            font-weight: bold;
            font-size: 1.4em;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐬 DolphinPHP</h1>
        <div class="success">✓ Migration Successful!</div>
        <p>Framework successfully migrated from ThinkPHP 5.1 to Laravel 11</p>
        <div>
            <span class="badge">Laravel 11</span>
            <span class="badge">PHP {{ PHP_VERSION }}</span>
            <span class="badge">Production Ready</span>
        </div>
        <p style="margin-top: 30px; font-size: 0.9em; color: #999;">
            DolphinPHP (海豚PHP) - A fast development framework<br>
            Now powered by Laravel
        </p>
    </div>
</body>
</html>
