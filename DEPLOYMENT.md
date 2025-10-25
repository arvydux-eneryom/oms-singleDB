# OMS Deployment Guide

This guide covers deploying the Operation Management System to production environments.

## Table of Contents
- [Pre-Deployment Checklist](#pre-deployment-checklist)
- [Server Requirements](#server-requirements)
- [Deployment Methods](#deployment-methods)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Web Server Configuration](#web-server-configuration)
- [Queue Workers](#queue-workers)
- [Scheduled Tasks](#scheduled-tasks)
- [SSL Configuration](#ssl-configuration)
- [Monitoring](#monitoring)
- [Backup Strategy](#backup-strategy)

## Pre-Deployment Checklist

### Code Quality
- [ ] All tests passing: `./test`
- [ ] Code style validated: `./vendor/bin/pint --test`
- [ ] Static analysis clean: `./vendor/bin/phpstan analyse`
- [ ] No TODO/FIXME comments in critical paths
- [ ] Dependency vulnerabilities checked: `composer audit`

### Environment
- [ ] Production `.env` configured
- [ ] All API keys and credentials secured
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Proper `APP_KEY` generated

### Database
- [ ] Production database created
- [ ] Migrations tested
- [ ] Backup strategy in place
- [ ] Database user with appropriate permissions

## Server Requirements

### Minimum Specifications
- **OS**: Ubuntu 20.04 LTS or newer (or equivalent)
- **RAM**: 2GB minimum, 4GB recommended
- **Disk**: 20GB minimum
- **CPU**: 2 cores recommended

### Software Requirements
```bash
# PHP 8.2 or higher
php -v

# Required PHP extensions
php -m | grep -E "dom|libxml|mbstring|pdo|xml|zip|gd|curl|fileinfo|tokenizer|json"

# Composer
composer --version

# Node.js & NPM (for asset building)
node --version
npm --version

# Database (choose one)
mysql --version
# or
psql --version

# Web server (choose one)
nginx -v
# or
apache2 -v

# Process manager
supervisord --version

# Redis (optional but recommended)
redis-server --version
```

## Deployment Methods

### Method 1: Traditional Server (VPS/Dedicated)

#### 1. Clone Repository
```bash
cd /var/www
git clone <your-repository-url> oms
cd oms
```

#### 2. Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
npm ci
npm run build
```

#### 3. Set Permissions
```bash
sudo chown -R www-data:www-data /var/www/oms
sudo chmod -R 755 /var/www/oms
sudo chmod -R 775 /var/www/oms/storage
sudo chmod -R 775 /var/www/oms/bootstrap/cache
```

#### 4. Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Method 2: Laravel Forge

1. Connect your server to Laravel Forge
2. Create new site pointing to `/public`
3. Configure environment variables
4. Enable Quick Deploy from repository
5. Configure queue workers and cron jobs

### Method 3: Laravel Vapor (Serverless)

1. Install Vapor CLI: `composer require laravel/vapor-cli`
2. Create `vapor.yml` configuration
3. Deploy: `vapor deploy production`

**Note**: Additional configuration needed for multi-tenancy in serverless environment.

### Method 4: Docker

See `docker-compose.yml` (if available) or create custom Docker setup:

```bash
# Build and run
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --force

# Optimize
docker-compose exec app php artisan optimize
```

## Environment Configuration

### Production .env Template

```env
# Application
APP_NAME="OMS"
APP_ENV=production
APP_KEY=base64:GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oms_production
DB_USERNAME=oms_user
DB_PASSWORD=secure_password_here

# Cache & Session
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=error

# Auto-logout
AUTH_INACTIVITY_ENABLED=true
AUTH_INACTIVITY_TIMEOUT=86400
AUTH_INACTIVITY_WARNING=300

# Integrations
TELEGRAM_API_ID=your_api_id
TELEGRAM_API_HASH=your_api_hash
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=your_phone_number
TWILIO_SMS_COMMON_URL=https://yourdomain.com
GOOGLE_MAPS_API_KEY=your_google_maps_key

# Monitoring
BUGSNAG_API_KEY=your_bugsnag_key
BUGSNAG_NOTIFY_RELEASE_STAGES=production,staging
APP_VERSION=1.0.0
```

### Security Best Practices

1. **Never commit `.env` file**
2. **Use strong passwords** for database users
3. **Restrict database access** to application server only
4. **Enable firewall** on server
5. **Use environment-specific keys** (different for staging/production)
6. **Rotate credentials** regularly

## Database Setup

### MySQL/PostgreSQL Setup

#### MySQL
```bash
# Create database
mysql -u root -p
CREATE DATABASE oms_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'oms_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON oms_production.* TO 'oms_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force
```

#### PostgreSQL
```bash
# Create database
sudo -u postgres psql
CREATE DATABASE oms_production;
CREATE USER oms_user WITH PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE oms_production TO oms_user;
\q

# Run migrations
php artisan migrate --force
```

### Migration Best Practices

1. **Always backup** before running migrations
2. **Test migrations** on staging first
3. **Use transactions** where possible
4. **Have rollback plan** ready
5. **Monitor migration execution**

```bash
# Backup before migration
mysqldump -u oms_user -p oms_production > backup_$(date +%Y%m%d_%H%M%S).sql

# Run migration with monitoring
php artisan migrate --force

# Rollback if needed (last batch)
php artisan migrate:rollback --force
```

## Web Server Configuration

### Nginx Configuration

Create `/etc/nginx/sites-available/oms`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com *.yourdomain.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com *.yourdomain.com;

    root /var/www/oms/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Logging
    access_log /var/log/nginx/oms_access.log;
    error_log /var/log/nginx/oms_error.log;

    # File upload limit
    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/oms /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Apache Configuration

Create `/etc/apache2/sites-available/oms.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias *.yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias *.yourdomain.com
    DocumentRoot /var/www/oms/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem

    <Directory /var/www/oms/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/oms_error.log
    CustomLog ${APACHE_LOG_DIR}/oms_access.log combined
</VirtualHost>
```

Enable site and modules:
```bash
sudo a2ensite oms
sudo a2enmod rewrite ssl
sudo systemctl reload apache2
```

## Queue Workers

### Supervisor Configuration

Create `/etc/supervisor/conf.d/oms-worker.conf`:

```ini
[program:oms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/oms/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/oms/storage/logs/worker.log
stopwaitsecs=3600
```

Start worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start oms-worker:*
```

Monitor workers:
```bash
sudo supervisorctl status oms-worker:*
sudo supervisorctl tail -f oms-worker:oms-worker_00 stdout
```

## Scheduled Tasks

Add to crontab:
```bash
sudo crontab -e -u www-data
```

Add line:
```cron
* * * * * cd /var/www/oms && php artisan schedule:run >> /dev/null 2>&1
```

Verify:
```bash
sudo crontab -l -u www-data
```

## SSL Configuration

### Using Let's Encrypt (Certbot)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d *.yourdomain.com

# Auto-renewal is set up automatically
# Test renewal:
sudo certbot renew --dry-run
```

### Certificate Renewal

Certbot automatically renews certificates. Verify:
```bash
sudo systemctl status certbot.timer
```

## Monitoring

### Application Monitoring

1. **BugSnag**: Configure in `.env`
2. **Laravel Telescope** (optional for staging):
   ```bash
   composer require laravel/telescope --dev
   php artisan telescope:install
   php artisan migrate
   ```

3. **Log Monitoring**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Server Monitoring

Monitor key metrics:
- CPU usage
- Memory usage
- Disk space
- Database connections
- Queue depth
- Response times

Recommended tools:
- New Relic
- Datadog
- Laravel Forge monitoring
- Custom Prometheus + Grafana

### Health Checks

Create monitoring endpoint or use:
```bash
php artisan route:list | grep health
```

## Backup Strategy

### Database Backups

#### Automated Daily Backup Script

Create `/usr/local/bin/oms-backup.sh`:

```bash
#!/bin/bash

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/oms"
DB_NAME="oms_production"
DB_USER="oms_user"
DB_PASS="secure_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Storage backup
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz /var/www/oms/storage/app

# Keep only last 7 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete
find $BACKUP_DIR -name "storage_*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

Make executable and schedule:
```bash
sudo chmod +x /usr/local/bin/oms-backup.sh
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/oms-backup.sh
```

### Remote Backup

Use services like:
- AWS S3
- Backblaze B2
- DigitalOcean Spaces
- Laravel Backup package

## Deployment Workflow

### Standard Deployment Process

```bash
# 1. Pull latest code
cd /var/www/oms
git pull origin main

# 2. Install/update dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# 3. Maintenance mode
php artisan down

# 4. Run migrations
php artisan migrate --force

# 5. Clear and cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Restart services
sudo supervisorctl restart oms-worker:*
php artisan queue:restart

# 7. Exit maintenance mode
php artisan up

# 8. Verify deployment
curl -I https://yourdomain.com
```

### Zero-Downtime Deployment

Use tools like:
- Laravel Envoyer
- Deployer
- Custom blue-green deployment script

## Troubleshooting

### Common Issues

1. **500 Server Error**
   ```bash
   php artisan optimize:clear
   tail -f storage/logs/laravel.log
   ```

2. **Permission Issues**
   ```bash
   sudo chown -R www-data:www-data /var/www/oms
   sudo chmod -R 755 /var/www/oms
   sudo chmod -R 775 /var/www/oms/storage
   ```

3. **Queue Not Processing**
   ```bash
   sudo supervisorctl status
   sudo supervisorctl restart oms-worker:*
   ```

4. **Database Connection Failed**
   - Verify credentials in `.env`
   - Check database service: `sudo systemctl status mysql`
   - Test connection: `mysql -u oms_user -p oms_production`

### Performance Optimization

1. **Enable OPcache** in `php.ini`:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=256
   opcache.interned_strings_buffer=16
   opcache.max_accelerated_files=10000
   opcache.revalidate_freq=60
   ```

2. **Redis for caching**:
   ```env
   CACHE_STORE=redis
   SESSION_DRIVER=redis
   ```

3. **Database indexing**:
   - Review slow query log
   - Add indexes to frequently queried columns

4. **CDN for assets**:
   - Use AWS CloudFront or Cloudflare
   - Configure in `config/filesystems.php`

## Security Hardening

1. **Firewall Configuration**:
   ```bash
   sudo ufw allow 22
   sudo ufw allow 80
   sudo ufw allow 443
   sudo ufw enable
   ```

2. **Fail2ban for SSH**:
   ```bash
   sudo apt install fail2ban
   sudo systemctl enable fail2ban
   ```

3. **Regular Updates**:
   ```bash
   sudo apt update && sudo apt upgrade
   composer update
   npm update
   ```

4. **Security Scanning**:
   ```bash
   composer audit
   npm audit
   ```

## Post-Deployment Verification

- [ ] Homepage loads correctly
- [ ] User authentication works
- [ ] Database queries executing
- [ ] Queue workers running
- [ ] Scheduled tasks configured
- [ ] Error monitoring active
- [ ] Backups running
- [ ] SSL certificate valid
- [ ] Email sending works
- [ ] Integrations functioning (Telegram, Twilio, Google Maps)

## Support

For deployment issues:
1. Check application logs: `storage/logs/laravel.log`
2. Check web server logs: `/var/log/nginx/` or `/var/log/apache2/`
3. Review server logs: `journalctl -xe`
4. Open GitHub issue with details

---

**Last Updated**: October 2024
