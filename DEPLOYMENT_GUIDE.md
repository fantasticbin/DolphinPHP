# DolphinPHP Deployment Guide - Laravel 11

## Production Deployment Instructions

Complete guide for deploying the migrated DolphinPHP application to production.

---

## 📋 Prerequisites

### Server Requirements

- **PHP**: 8.2 or higher
- **Web Server**: Nginx or Apache
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Composer**: Latest version
- **Node.js**: 18+ (for asset compilation)
- **Memory**: Minimum 512MB RAM
- **Disk Space**: Minimum 1GB

### PHP Extensions Required

```bash
php -m | grep -E 'BCMath|Ctype|Fileinfo|JSON|Mbstring|OpenSSL|PDO|Tokenizer|XML|cURL|GD|zip'
```

Required extensions:
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- cURL
- GD
- zip

---

## 🚀 Deployment Steps

### 1. Clone Repository

```bash
cd /var/www
git clone https://github.com/fantasticbin/DolphinPHP.git
cd DolphinPHP
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm install

# Build assets
npm run build
```

### 3. Environment Configuration

```bash
# Copy environment file
cp laravel-app/.env.example laravel-app/.env

# Generate application key
php laravel-app/artisan key:generate
```

Edit `.env` file:

```env
APP_NAME=DolphinPHP
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dolphinphp
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Set Permissions

```bash
# Storage and cache directories
chmod -R 775 laravel-app/storage
chmod -R 775 laravel-app/bootstrap/cache

# Set ownership (replace www-data with your web server user)
chown -R www-data:www-data laravel-app/storage
chown -R www-data:www-data laravel-app/bootstrap/cache
```

### 5. Database Migration

```bash
# Create database
mysql -u root -p << EOF
CREATE DATABASE dolphinphp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dolphinphp_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON dolphinphp.* TO 'dolphinphp_user'@'localhost';
FLUSH PRIVILEGES;
EOF

# Run migrations
php laravel-app/artisan migrate --force

# (Optional) Seed database
php laravel-app/artisan db:seed --force
```

### 6. Optimize Application

```bash
# Cache configuration
php laravel-app/artisan config:cache

# Cache routes
php laravel-app/artisan route:cache

# Cache views
php laravel-app/artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## 🌐 Web Server Configuration

### Nginx Configuration

Create `/etc/nginx/sites-available/dolphinphp`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/DolphinPHP/laravel-app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Asset optimization
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable site:

```bash
ln -s /etc/nginx/sites-available/dolphinphp /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### Apache Configuration

Create `/etc/apache2/sites-available/dolphinphp.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAdmin admin@yourdomain.com
    DocumentRoot /var/www/DolphinPHP/laravel-app/public

    <Directory /var/www/DolphinPHP/laravel-app/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Asset optimization
    <FilesMatch "\.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$">
        Header set Cache-Control "max-age=31536000, public, immutable"
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/dolphinphp_error.log
    CustomLog ${APACHE_LOG_DIR}/dolphinphp_access.log combined
</VirtualHost>
```

Enable site:

```bash
a2ensite dolphinphp
a2enmod rewrite headers
systemctl reload apache2
```

---

## 🔒 SSL/HTTPS Setup

### Using Certbot (Let's Encrypt)

```bash
# Install Certbot
apt install certbot python3-certbot-nginx

# Obtain certificate (Nginx)
certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Or for Apache
# certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
certbot renew --dry-run
```

### Manual SSL Configuration

Add to Nginx config:

```nginx
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers on;
    
    # ... rest of config
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

---

## 📊 Monitoring & Logging

### Application Logs

```bash
# View logs
tail -f laravel-app/storage/logs/laravel.log

# Clear old logs
php laravel-app/artisan log:clear
```

### Enable Laravel Telescope (Optional)

```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

### System Monitoring

```bash
# Install monitoring tools
apt install htop iotop nethogs

# Monitor PHP-FPM
systemctl status php8.3-fpm

# Monitor MySQL
mysqladmin -u root -p processlist
```

---

## ⚙️ Performance Optimization

### OPcache Configuration

Edit `/etc/php/8.3/fpm/php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### Redis Configuration

Install and configure Redis:

```bash
apt install redis-server
systemctl enable redis-server
systemctl start redis-server

# Test connection
redis-cli ping
```

### MySQL Optimization

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
max_connections = 200
query_cache_size = 0
query_cache_type = 0
```

---

## 🔄 Updates & Maintenance

### Updating Application

```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Database Backup

```bash
# Manual backup
mysqldump -u root -p dolphinphp > backup_$(date +%Y%m%d_%H%M%S).sql

# Automated backup (cron)
0 2 * * * /usr/bin/mysqldump -u backup_user -pPASSWORD dolphinphp | gzip > /backups/dolphinphp_$(date +\%Y\%m\%d).sql.gz
```

### Laravel Backup Package

```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
php artisan backup:run
```

---

## 🛡️ Security Checklist

- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong, unique `APP_KEY`
- [ ] Configure HTTPS/SSL
- [ ] Set proper file permissions (755/644)
- [ ] Enable firewall (UFW/iptables)
- [ ] Use strong database passwords
- [ ] Enable fail2ban for SSH
- [ ] Regular security updates
- [ ] Monitor error logs
- [ ] Implement rate limiting
- [ ] Use Redis for sessions/cache
- [ ] Configure CORS properly
- [ ] Set security headers

---

## 🚨 Troubleshooting

### 500 Internal Server Error

```bash
# Check Laravel logs
tail -f laravel-app/storage/logs/laravel.log

# Check web server logs
tail -f /var/log/nginx/error.log
```

### Permission Issues

```bash
# Fix permissions
chmod -R 775 laravel-app/storage
chmod -R 775 laravel-app/bootstrap/cache
chown -R www-data:www-data laravel-app/storage
```

### Database Connection Issues

```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();
```

### Cache Issues

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 📞 Support

For issues or questions:
- Review Laravel documentation
- Check application logs
- Consult migration documentation
- Contact system administrator

---

**Last Updated**: Phase 6 Complete  
**Laravel Version**: 11.46.1  
**Tested On**: Ubuntu 22.04 LTS, PHP 8.3
