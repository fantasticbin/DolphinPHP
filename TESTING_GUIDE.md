# Testing Guide for DolphinPHP Laravel 11 Migration

Complete guide for testing the migrated DolphinPHP application on Laravel 11.

## Table of Contents

- [Introduction](#introduction)
- [Setup](#setup)
- [Running Tests](#running-tests)
- [Writing Tests](#writing-tests)
- [Test Coverage](#test-coverage)
- [Continuous Integration](#continuous-integration)

## Introduction

The DolphinPHP migration includes a comprehensive testing suite with:

- **Unit Tests**: Testing models, services, and utilities
- **Feature Tests**: Testing HTTP endpoints and controllers
- **Browser Tests**: Testing UI flows (optional with Laravel Dusk)

## Setup

### 1. Install PHPUnit

PHPUnit is included with Laravel. Verify installation:

```bash
cd laravel-app
./vendor/bin/phpunit --version
```

### 2. Configure Testing Environment

The `phpunit.xml` file is already configured with:
- SQLite in-memory database for fast tests
- Array cache driver
- Testing environment variables

### 3. Create Test Database (Optional)

For MySQL testing:

```bash
# Create test database
mysql -u root -p -e "CREATE DATABASE dolphinphp_test"

# Update phpunit.xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value="dolphinphp_test"/>
```

## Running Tests

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suite

```bash
# Run only Unit tests
php artisan test --testsuite=Unit

# Run only Feature tests
php artisan test --testsuite=Feature
```

### Run Specific Test File

```bash
php artisan test tests/Feature/LoginControllerTest.php
```

### Run Specific Test Method

```bash
php artisan test --filter test_user_can_login
```

### Run Tests with Coverage

```bash
php artisan test --coverage
```

### Run Tests in Parallel

```bash
php artisan test --parallel
```

## Writing Tests

### Unit Tests

Unit tests focus on testing individual classes and methods in isolation.

**Example: Testing User Model**

```php
<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_belongs_to_role()
    {
        $role = Role::create(['name' => 'Admin', 'status' => 1]);
        
        $user = User::create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 1,
        ]);

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals($role->id, $user->role->id);
    }

    public function test_active_scope_filters_active_users()
    {
        $role = Role::create(['name' => 'Admin', 'status' => 1]);
        
        User::create([
            'username' => 'active',
            'email' => 'active@test.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 1,
        ]);

        User::create([
            'username' => 'inactive',
            'email' => 'inactive@test.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 0,
        ]);

        $activeUsers = User::active()->get();

        $this->assertCount(1, $activeUsers);
    }
}
```

### Feature Tests

Feature tests verify HTTP endpoints and controller logic.

**Example: Testing Login Controller**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_displays()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_user_can_login_with_correct_credentials()
    {
        $role = Role::create(['name' => 'Admin', 'status' => 1]);
        
        User::create([
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 1,
        ]);

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin');
    }

    public function test_user_cannot_login_with_incorrect_password()
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }
}
```

**Example: Testing User Controller**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $role = Role::create(['name' => 'Admin', 'status' => 1]);
        
        $this->adminUser = User::create([
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 1,
        ]);
    }

    public function test_user_list_displays()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/user');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.user.index');
    }

    public function test_user_can_be_created()
    {
        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/user', [
                             'username' => 'newuser',
                             'email' => 'new@test.com',
                             'password' => 'password123',
                             'role_id' => $this->adminUser->role_id,
                             'status' => 1,
                         ]);

        $this->assertDatabaseHas('admin_user', [
            'username' => 'newuser',
            'email' => 'new@test.com',
        ]);
    }

    public function test_guest_cannot_access_user_management()
    {
        $response = $this->get('/admin/user');
        
        $response->assertRedirect('/login');
    }
}
```

### Browser Tests (Laravel Dusk)

For testing JavaScript interactions and UI flows, install Laravel Dusk:

```bash
composer require --dev laravel/dusk
php artisan dusk:install
```

**Example: Testing Login Flow**

```php
<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Role;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    public function test_user_can_login()
    {
        $role = Role::create(['name' => 'Admin', 'status' => 1]);
        
        $user = User::create([
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 1,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('username', 'admin')
                    ->type('password', 'password')
                    ->press('Login')
                    ->assertPathIs('/admin')
                    ->assertSee('Dashboard');
        });
    }
}
```

## Test Coverage

### Generate Coverage Report

```bash
# HTML coverage report
php artisan test --coverage-html coverage

# Open coverage/index.html in browser
```

### Coverage Metrics

Aim for:
- **Models**: 90%+ coverage
- **Controllers**: 80%+ coverage
- **Services**: 85%+ coverage
- **Helpers**: 90%+ coverage

## Continuous Integration

### GitHub Actions

Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.3
        extensions: mbstring, pdo, pdo_mysql
    
    - name: Install Dependencies
      run: |
        cd laravel-app
        composer install --no-interaction
    
    - name: Run Tests
      run: |
        cd laravel-app
        php artisan test --coverage
```

### GitLab CI

Create `.gitlab-ci.yml`:

```yaml
test:
  image: php:8.3
  
  before_script:
    - apt-get update && apt-get install -y git unzip
    - curl -sS https://getcomposer.org/installer | php
    - cd laravel-app && php composer.phar install
  
  script:
    - cd laravel-app
    - php artisan test --coverage
```

## Best Practices

### 1. Test Naming

Use descriptive names that explain what is being tested:

```php
✓ test_user_can_login_with_correct_credentials()
✗ testLogin()
```

### 2. Arrange-Act-Assert Pattern

```php
public function test_user_can_be_created()
{
    // Arrange
    $userData = ['username' => 'test', ...];
    
    // Act
    $user = User::create($userData);
    
    // Assert
    $this->assertDatabaseHas('admin_user', $userData);
}
```

### 3. Use setUp() for Common Setup

```php
protected function setUp(): void
{
    parent::setUp();
    
    // Create common test data
    $this->role = Role::create([...]);
}
```

### 4. Clean Test Database

Always use `RefreshDatabase` trait:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;
}
```

### 5. Test Edge Cases

```php
public function test_user_cannot_be_created_with_duplicate_email()
{
    User::create(['email' => 'test@test.com', ...]);
    
    $this->expectException(QueryException::class);
    User::create(['email' => 'test@test.com', ...]);
}
```

## Testing Checklist

- [ ] All models have unit tests for relationships
- [ ] All models have unit tests for scopes
- [ ] All controllers have feature tests for CRUD operations
- [ ] Authentication flows are tested
- [ ] Authorization checks are tested
- [ ] Form validation is tested
- [ ] API endpoints are tested
- [ ] Edge cases are covered
- [ ] Error handling is tested
- [ ] Tests run successfully in CI/CD

## Additional Resources

- [Laravel Testing Documentation](https://laravel.com/docs/11.x/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Dusk Documentation](https://laravel.com/docs/11.x/dusk)
- [Pest Testing Framework](https://pestphp.com/) (alternative to PHPUnit)

---

**Testing Status**: ✅ Complete testing framework implemented!
