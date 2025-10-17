# DolphinPHP Upgrade Guide - ThinkPHP to Laravel

## Developer Guide for the Laravel 11 Migration

This guide helps developers understand the changes made during the migration from ThinkPHP 5.1 to Laravel 11.

---

## 🔄 Core Changes

### 1. Namespace Changes

**Before (ThinkPHP):**
```php
namespace app\admin\controller;
use think\Controller;

class User extends Controller
{
    //
}
```

**After (Laravel):**
```php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    //
}
```

### 2. Model Changes

**Before (ThinkPHP):**
```php
namespace app\common\model;
use think\Model;

class User extends Model
{
    protected $name = 'admin_user';
    
    public function getList()
    {
        return $this->where('status', 1)->select();
    }
}
```

**After (Laravel):**
```php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'admin_user';
    protected $fillable = ['username', 'email', 'password'];
    
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
```

### 3. Query Builder

**Before (ThinkPHP):**
```php
// Get records
$users = Db::name('user')->where('status', 1)->select();

// Insert
Db::name('user')->insert($data);

// Update
Db::name('user')->where('id', 1)->update($data);

// Delete
Db::name('user')->where('id', 1)->delete();
```

**After (Laravel):**
```php
// Get records
$users = User::where('status', 1)->get();

// Insert
User::create($data);

// Update
User::find(1)->update($data);

// Delete
User::find(1)->delete();
```

### 4. Routing

**Before (ThinkPHP):**
```php
// route/admin.php
return [
    'admin/user/index' => 'admin/User/index',
    'admin/user/add'   => 'admin/User/add',
];
```

**After (Laravel):**
```php
// routes/web.php
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/user', [UserController::class, 'index']);
    Route::post('/user', [UserController::class, 'store']);
    Route::put('/user/{id}', [UserController::class, 'update']);
    Route::delete('/user/{id}', [UserController::class, 'destroy']);
});
```

### 5. Views/Templates

**Before (ThinkPHP):**
```html
{extend name="admin@layout/base" /}

{block name="content"}
<div class="page-content">
    {volist name="list" id="vo"}
    <div>{$vo.title}</div>
    {/volist}
</div>
{/block}
```

**After (Laravel Blade):**
```blade
@extends('layouts.admin')

@section('content')
<div class="page-content">
    @foreach($list as $item)
    <div>{{ $item->title }}</div>
    @endforeach
</div>
@endsection
```

### 6. Request/Response

**Before (ThinkPHP):**
```php
// Get input
$username = input('username');
$page = input('page/d', 1);

// Return JSON
return json(['code' => 1, 'msg' => 'Success', 'data' => $data]);

// Return view
return view('index', ['data' => $data]);
```

**After (Laravel):**
```php
// Get input
$username = request('username');
$page = request('page', 1);

// Return JSON
return response()->json([
    'success' => true,
    'message' => 'Success',
    'data' => $data
]);

// Return view
return view('admin.index', ['data' => $data]);
```

### 7. Validation

**Before (ThinkPHP):**
```php
$validate = new \app\admin\validate\User;
if (!$validate->check($data)) {
    return json(['code' => 0, 'msg' => $validate->getError()]);
}
```

**After (Laravel):**
```php
$validated = $request->validate([
    'username' => 'required|unique:users|max:255',
    'email' => 'required|email',
    'password' => 'required|min:6',
]);
```

### 8. Configuration

**Before (ThinkPHP):**
```php
// Get config
$value = config('app.name');

// Set config
config('app.name', 'New Name');
```

**After (Laravel):**
```php
// Get config
$value = config('app.name');

// Set config (runtime only)
config(['app.name' => 'New Name']);

// For permanent storage, use database or cache
Config::set('key', 'value');
```

### 9. Session

**Before (ThinkPHP):**
```php
// Set session
session('user_id', 1);

// Get session
$userId = session('user_id');

// Delete session
session('user_id', null);
```

**After (Laravel):**
```php
// Set session
session(['user_id' => 1]);

// Get session
$userId = session('user_id');

// Delete session
session()->forget('user_id');
```

### 10. Cache

**Before (ThinkPHP):**
```php
// Set cache
cache('key', 'value', 3600);

// Get cache
$value = cache('key');

// Delete cache
cache('key', null);
```

**After (Laravel):**
```php
// Set cache
Cache::put('key', 'value', now()->addHours(1));

// Get cache
$value = Cache::get('key');

// Delete cache
Cache::forget('key');
```

---

## 🗄️ Database Migrations

### Running Migrations

```bash
# Run all migrations
php laravel-app/artisan migrate

# Rollback last migration
php laravel-app/artisan migrate:rollback

# Reset all migrations
php laravel-app/artisan migrate:reset

# Fresh migration (drop all tables and remigrate)
php laravel-app/artisan migrate:fresh
```

---

## 🔐 Authentication

### Login Implementation

**Before (ThinkPHP):**
```php
public function login()
{
    $username = input('username');
    $password = input('password');
    
    $user = User::where('username', $username)->find();
    if ($user && password_verify($password, $user['password'])) {
        session('user_id', $user['id']);
        return json(['code' => 1, 'msg' => 'Login success']);
    }
    return json(['code' => 0, 'msg' => 'Invalid credentials']);
}
```

**After (Laravel):**
```php
public function login(Request $request)
{
    $credentials = $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);
    
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return response()->json([
            'success' => true,
            'message' => 'Login successful'
        ]);
    }
    
    return response()->json([
        'success' => false,
        'message' => 'Invalid credentials'
    ], 401);
}
```

---

## 🛠️ Helper Functions

### Custom Helpers

The migrated app includes custom helpers in `app/Helpers/functions.php`:

- `get_client_ip()` - Get client IP address
- `admin_url()` - Generate admin URL
- `home_url()` - Generate home URL
- `get_nickname()` - Get user nickname
- `action_log()` - Record action log
- `get_browser_type()` - Detect browser
- `role_auth()` - Get role permissions
- `config_cache()` - Get cached config

---

## 📦 Composer Packages

### New Dependencies

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "laravel/tinker": "^2.9"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/pint": "^1.13",
        "laravel/sail": "^1.26",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.0",
        "phpunit/phpunit": "^11.0",
        "spatie/laravel-ignition": "^2.4"
    }
}
```

---

## 🚀 Artisan Commands

### Useful Commands

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate app key
php artisan key:generate

# Run development server
php artisan serve

# View routes
php artisan route:list

# Run tinker (REPL)
php artisan tinker

# Make commands
php artisan make:model ModelName
php artisan make:controller ControllerName
php artisan make:migration create_table_name
php artisan make:middleware MiddlewareName
```

---

## 🎨 Frontend Assets

### Asset Compilation

```bash
# Install dependencies
npm install

# Development build
npm run dev

# Production build
npm run build

# Watch for changes
npm run watch
```

---

## ⚠️ Common Pitfalls

1. **Namespace Case Sensitivity**: Laravel uses `App\` (capital A) not `app\`
2. **Route Names**: Use named routes for easier maintenance
3. **Mass Assignment**: Always define `$fillable` or `$guarded` in models
4. **CSRF Tokens**: Required for all POST/PUT/DELETE requests
5. **Query Builder**: Eloquent methods return Collections, not arrays
6. **Blade Escaping**: `{{ }}` auto-escapes, use `{!! !!}` for HTML
7. **Environment Variables**: Use `.env` file, not config files directly

---

## 📚 Resources

- [Laravel 11 Documentation](https://laravel.com/docs/11.x)
- [Laravel Eloquent ORM](https://laravel.com/docs/11.x/eloquent)
- [Laravel Blade Templates](https://laravel.com/docs/11.x/blade)
- [Laravel Routing](https://laravel.com/docs/11.x/routing)
- [Laravel Validation](https://laravel.com/docs/11.x/validation)

---

## 🤝 Getting Help

- Check Laravel documentation first
- Search Laravel forums and Stack Overflow
- Review existing migrated code for patterns
- Consult migration documentation files

---

**Last Updated**: Phase 6 Complete  
**Laravel Version**: 11.46.1  
**Compatibility**: PHP 8.2+
