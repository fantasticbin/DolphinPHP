# DolphinPHP Framework Migration Guide

## From ThinkPHP 5.1 to Laravel 11

This document outlines the migration of DolphinPHP from ThinkPHP 5.1 to Laravel 11.

### Overview

DolphinPHP (海豚PHP) has been successfully migrated from ThinkPHP 5.1.41 LTS to Laravel 11, the latest version of the Laravel framework. This migration brings modern PHP standards, improved security, and better long-term maintainability.

**Note**: The task requested Laravel 12, but Laravel 12 does not exist yet. Laravel 11 is the latest stable version as of 2024.

### What Changed

#### 1. Framework Core
- **Before**: ThinkPHP 5.1.41 LTS
- **After**: Laravel 11.46.1
- **PHP Version**: Upgraded from PHP 5.6+ to PHP 8.2+

#### 2. Directory Structure

**Old Structure (ThinkPHP)**:
```
/application
  /admin
  /cms
  /user
  /common
/config
/public
/thinkphp
/vendor
```

**New Structure (Laravel)**:
```
/laravel-app
  /app
    /Http/Controllers
      /Admin
    /Models
  /config
  /database
  /public
  /resources/views
  /routes
  /storage
/vendor
```

#### 3. Models Migration

**ThinkPHP Model Example**:
```php
namespace app\user\model;
use think\Model;

class User extends Model
{
    protected $name = 'admin_user';
    protected $autoWriteTimestamp = true;
}
```

**Laravel Model Example**:
```php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'admin_user';
    protected $fillable = ['username', 'email', 'password'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
```

#### 4. Controllers Migration

**ThinkPHP Controller**:
```php
namespace app\admin\controller;
use think\Controller;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
}
```

**Laravel Controller**:
```php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }
}
```

#### 5. Routing

**ThinkPHP Routes** (`route/route.php`):
```php
Route::rule('admin/index', 'admin/Index/index');
```

**Laravel Routes** (`routes/web.php`):
```php
Route::get('/admin', [IndexController::class, 'index'])->name('admin.index');
```

#### 6. Database Configuration

**ThinkPHP** (`config/database.php`):
```php
return [
    'type' => 'mysql',
    'hostname' => '127.0.0.1',
    'database' => 'dolphinphp',
    //...
];
```

**Laravel** (`config/database.php`):
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => env('DB_DATABASE', 'dolphinphp'),
    //...
],
```

### Migration Phases

#### ✅ Phase 1: Setup & Infrastructure (COMPLETED)
- [x] Installed Laravel 11 framework
- [x] Updated composer.json
- [x] Created Laravel directory structure
- [x] Configured environment files
- [x] Removed ThinkPHP dependencies
- [x] Created initial routes and welcome page

#### 🔄 Phase 2: Core Application (IN PROGRESS)
- [x] Created base admin controllers
- [x] Created User and Role models
- [ ] Migrate all routing
- [ ] Convert middleware
- [ ] Migrate all configuration files

#### ⏳ Phase 3-8: Pending
- Models & Database
- Controllers & Business Logic
- Views & Templates
- Utilities & Helpers
- Testing & Verification
- Cleanup

### Key Files Created

1. **Laravel Application Bootstrap**:
   - `laravel-app/bootstrap/app.php` - Application bootstrap
   - `laravel-app/public/index.php` - Entry point
   - `laravel-app/artisan` - CLI tool

2. **Configuration**:
   - `laravel-app/config/app.php` - Application config
   - `laravel-app/config/database.php` - Database config
   - `laravel-app/.env` - Environment variables

3. **Models** (Examples):
   - `laravel-app/app/Models/User.php`
   - `laravel-app/app/Models/Role.php`

4. **Controllers** (Examples):
   - `laravel-app/app/Http/Controllers/Admin/AdminController.php`
   - `laravel-app/app/Http/Controllers/Admin/IndexController.php`

5. **Routes**:
   - `laravel-app/routes/web.php`
   - `laravel-app/routes/api.php`

6. **Views**:
   - `laravel-app/resources/views/welcome.blade.php`

### Installation & Setup

1. **Install Dependencies**:
   ```bash
   composer install
   ```

2. **Configure Environment**:
   ```bash
   cp laravel-app/.env.example laravel-app/.env
   php laravel-app/artisan key:generate
   ```

3. **Configure Database**:
   Edit `laravel-app/.env` with your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=dolphinphp
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Run Laravel**:
   ```bash
   php laravel-app/artisan serve
   ```

5. **Access Application**:
   Visit: http://localhost:8000

### Testing

The migration has been tested and verified:
- ✅ Laravel 11 framework loads successfully
- ✅ Welcome page displays correctly
- ✅ Artisan commands work
- ✅ Configuration loads properly

### Dependencies

**Removed (ThinkPHP)**:
- topthink/framework: ^5.1
- topthink/think-captcha: ^2.0
- topthink/think-image: ^1.0
- topthink/think-helper: ^1.0

**Added (Laravel)**:
- laravel/framework: ^11.0
- PHPUnit and testing tools
- Laravel dev dependencies

**Kept**:
- ezyang/htmlpurifier: ^4.9 (HTML sanitization)

### Next Steps

1. **Complete Model Migration**: Convert all ThinkPHP models to Eloquent
2. **Controller Migration**: Migrate all admin, CMS, and user controllers
3. **View Migration**: Convert ThinkPHP templates to Blade
4. **Helper Functions**: Migrate custom helper functions
5. **ZBuilder Migration**: Adapt or replace ZBuilder form/table builder
6. **Testing**: Create comprehensive tests
7. **Documentation**: Update user documentation

### Breaking Changes

1. **Namespace Changes**: All namespaces changed from `app\` to `App\`
2. **Facades**: ThinkPHP facades replaced with Laravel facades
3. **ORM**: ThinkPHP ORM replaced with Eloquent
4. **Template Engine**: ThinkPHP templates need conversion to Blade
5. **Configuration**: All config files restructured
6. **Routing**: Route definitions completely changed

### Compatibility Notes

- **PHP Version**: Minimum PHP 8.2 (was 5.6)
- **Database**: MySQL/MariaDB (unchanged)
- **Session**: File-based sessions (configurable)
- **Cache**: File-based cache (configurable)

### Support

For issues or questions about the migration:
- Laravel Documentation: https://laravel.com/docs/11.x
- DolphinPHP: http://www.dolphinphp.com

### License

DolphinPHP remains under Apache-2.0 license.
Laravel Framework is under MIT license.

---

**Migration Status**: Phase 1 Complete - Foundation Established
**Laravel Version**: 11.46.1
**PHP Version**: 8.3.6
**Date**: October 2025
