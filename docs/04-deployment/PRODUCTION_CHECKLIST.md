# 🚀 Production Deployment Checklist

Complete guide to deploy Padi REST API Framework to production safely and securely.

---

## 📋 Pre-Deployment Checklist

### 1. Environment Configuration

**Critical Settings:**

```bash
# .env (Production)
APP_ENV=production
APP_DEBUG=false
APP_NAME="Your API Name"
APP_URL=https://api.yourdomain.com

# Database (Use production credentials)
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your_production_db
DB_USERNAME=your_db_user
DB_PASSWORD=strong_password_here

# JWT (Generate new secret)
JWT_SECRET=generate_64_char_random_string_here
JWT_ALGORITHM=HS256
JWT_EXPIRATION=3600

# CORS (Whitelist your domains)
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE
CORS_ALLOWED_HEADERS=Content-Type,Authorization

# Rate Limiting (Adjust for production)
RATE_LIMIT_MAX=30
RATE_LIMIT_WINDOW=60

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Frontend URL (for password reset links)
FRONTEND_URL=https://app.yourdomain.com
```

---

### 2. Security Hardening

#### ✅ Generate Strong JWT Secret

```bash
# Generate 64-character random secret
php -r "echo bin2hex(random_bytes(32));"

# Update .env
JWT_SECRET=your_generated_secret_here
```

#### ✅ Verify Security Settings

```php
// Check core/Auth.php validates JWT_SECRET length (minimum 32 chars)
// Already implemented ✓

// Verify password requirements
// app/Controllers/AuthController.php - password complexity ✓

// Check rate limiting is enabled
// app/Middleware/RateLimitMiddleware.php ✓
```

#### ✅ Disable Debug Mode

```bash
APP_DEBUG=false
```

⚠️ **Critical:** Debug mode exposes sensitive information (stack traces, database queries, environment variables)

#### ✅ Configure CORS Properly

```bash
# Don't use wildcards in production
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com

# Not recommended for production:
# CORS_ALLOWED_ORIGINS=*
```

---

### 3. Database

#### ✅ Production Database Setup

```bash
# Use separate production database
DB_DATABASE=production_db

# Strong password
DB_PASSWORD=use_strong_password_minimum_16_chars

# Restricted user (not root)
# Grant only necessary permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON production_db.* TO 'app_user'@'localhost';
```

#### ✅ Run Migrations

```bash
php scripts/migrate.php migrate
```

#### ✅ Backup Strategy

**Automated Daily Backups:**

```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/database"
DB_NAME="production_db"

# Create backup
mysqldump -u root -p $DB_NAME > $BACKUP_DIR/backup_$DATE.sql

# Compress
gzip $BACKUP_DIR/backup_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete

echo "Backup completed: backup_$DATE.sql.gz"
```

**Schedule with cron:**

```bash
# Run daily at 2 AM
0 2 * * * /path/to/backup.sh
```

---

### 4. Web Server Configuration

#### Option A: Nginx + PHP-FPM (Recommended)

**nginx.conf:**

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;

    root /var/www/padi-api/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/your_cert.pem;
    ssl_certificate_key /etc/ssl/private/your_key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Logging
    access_log /var/log/nginx/api_access.log;
    error_log /var/log/nginx/api_error.log;

    # Rate Limiting (additional layer)
    limit_req_zone $binary_remote_addr zone=api_limit:10m rate=10r/s;
    limit_req zone=api_limit burst=20 nodelay;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ ^/(vendor|database|scripts|tests) {
        deny all;
    }
}
```

#### Option B: Apache + mod_php

**.htaccess (in public/):**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

### 5. SSL/HTTPS Setup

#### Using Let's Encrypt (Free)

```bash
# Install certbot
sudo apt-get install certbot python3-certbot-nginx

# Generate certificate
sudo certbot --nginx -d api.yourdomain.com

# Auto-renewal (cron)
0 3 * * * certbot renew --quiet
```

#### Force HTTPS in Application

Add middleware or modify `public/index.php`:

```php
// Force HTTPS in production
if ($_ENV['APP_ENV'] === 'production' &&
    (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}
```

---

### 6. Monitoring & Logging

#### Setup Error Logging

**Create log directory:**

```bash
mkdir -p storage/logs
chmod 755 storage/logs
```

**Enable PHP error logging:**

```ini
; php.ini
log_errors = On
error_log = /var/www/padi-api/storage/logs/php_error.log
```

#### Health Check Endpoint

**Add to routes/api.php:**

```php
$router->get('/health', function () {
    $response = new \Core\Response();

    $health = [
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'database' => 'unknown',
        'cache' => 'unknown'
    ];

    // Check database
    try {
        $db = \Core\Database::getInstance()->getConnection();
        $db->query('SELECT 1');
        $health['database'] = 'connected';
    } catch (Exception $e) {
        $health['status'] = 'unhealthy';
        $health['database'] = 'disconnected';
    }

    // Check Redis (if used)
    try {
        if (class_exists('Redis')) {
            $redis = new Redis();
            $redis->connect('localhost', 6379);
            $redis->ping();
            $health['cache'] = 'connected';
        }
    } catch (Exception $e) {
        $health['cache'] = 'disconnected';
    }

    $statusCode = $health['status'] === 'healthy' ? 200 : 503;
    $response->json($health, $statusCode);
});
```

#### Monitor with Uptime Robot or Similar

- https://uptimerobot.com/
- Monitor `/health` endpoint every 5 minutes
- Alert if down

---

### 7. Performance Optimization

#### Enable OPcache

```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

#### Database Indexing

```sql
-- Ensure indexes on frequently queried columns
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_password_resets_token ON password_resets(token);
CREATE INDEX idx_password_resets_email ON password_resets(email);
```

#### Enable Gzip Compression

```nginx
# nginx
gzip on;
gzip_vary on;
gzip_types text/plain application/json application/javascript text/css;
```

---

### 8. Rate Limiting Tuning

Adjust for production load:

```bash
# .env - More restrictive for production
RATE_LIMIT_MAX=30        # 30 requests
RATE_LIMIT_WINDOW=60     # per minute
```

For authenticated endpoints, consider different limits:

```php
// Example: Different limits for auth vs public
public function handle(Request $request): void
{
    $maxRequests = $request->bearerToken() ? 100 : 30;
    // ... rate limit logic
}
```

---

### 9. API Versioning Strategy

#### Implementation Options:

**Option A: URL Versioning (Recommended)**

```php
// routes/api.php
$router->version('1', function($router) {
    $router->group(['prefix' => 'auth'], function ($router) {
        $router->post('/register', 'AuthController@register');
        $router->post('/login', 'AuthController@login');
    });

    $router->group(['prefix' => 'users', 'middleware' => ['AuthMiddleware']], function ($router) {
        $router->get('/', 'UserController@index');
        $router->get('/{id}', 'UserController@show');
    });
});

// Access as: https://api.yourdomain.com/v1/auth/login
```

**Option B: Header Versioning**

```php
// Middleware to check API version
class ApiVersionMiddleware {
    public function handle(Request $request): void {
        $version = $request->header('Api-Version', 'v1');

        if (!in_array($version, ['v1', 'v2'])) {
            $response = new Response();
            $response->json([
                'success' => false,
                'message' => 'Unsupported API version'
            ], 400);
        }

        $request->apiVersion = $version;
    }
}
```

**Best Practice:**

- Use URL versioning for simplicity
- Major versions only (v1, v2, v3)
- Keep v1 running for backward compatibility
- Deprecation notice 6 months before removal

```php
// Deprecation warning in response
$response = [
    'success' => true,
    'data' => $data,
    '_meta' => [
        'version' => 'v1',
        'deprecated' => true,
        'sunset_date' => '2026-12-31',
        'message' => 'This API version will be sunset on 2026-12-31. Please migrate to v2.'
    ]
];
```

---

### 10. Testing Strategy

#### Setup PHPUnit

Framework already includes PHPUnit. Configure for production testing:

**Run existing tests:**

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit tests/UserTest.php

# With coverage
./vendor/bin/phpunit --coverage-html coverage
```

#### Create Integration Tests

**tests/Integration/AuthTest.php:**

```php
<?php

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private string $baseUrl = 'http://localhost:8085';

    public function testRegisterSuccess()
    {
        $response = $this->post('/auth/register', [
            'username' => 'testuser_' . time(),
            'email' => 'test_' . time() . '@example.com',
            'password' => 'Test123!@#',
            'password_confirmation' => 'Test123!@#'
        ]);

        $this->assertEquals(201, $response['status']);
        $this->assertTrue($response['data']['success']);
    }

    public function testLoginSuccess()
    {
        $response = $this->post('/auth/login', [
            'username' => 'test@example.com',
            'password' => 'Test123!@#'
        ]);

        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('token', $response['data']['data']);
    }

    public function testRateLimiting()
    {
        // Make 35 requests (exceeds limit of 30)
        for ($i = 0; $i < 35; $i++) {
            $response = $this->post('/auth/login', [
                'username' => 'test@example.com',
                'password' => 'wrong'
            ]);
        }

        // Should be rate limited
        $this->assertEquals(429, $response['status']);
    }

    private function post($endpoint, $data)
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $status,
            'data' => json_decode($response, true)
        ];
    }
}
```

#### Automated Testing Pipeline

**Create test script:**

```bash
#!/bin/bash
# tests/run_production_tests.sh

echo "🧪 Running Production Tests..."

# Unit tests
echo "Running unit tests..."
./vendor/bin/phpunit tests/Unit

# Integration tests
echo "Running integration tests..."
./vendor/bin/phpunit tests/Integration

# API health check
echo "Testing API health..."
curl -f http://localhost:8085/health || exit 1

# Database connection
echo "Testing database connection..."
php -r "
require 'vendor/autoload.php';
Core\Env::load('.env');
try {
    \$db = Core\Database::getInstance()->getConnection();
    \$db->query('SELECT 1');
    echo '✓ Database connection OK\n';
} catch (Exception \$e) {
    echo '✗ Database connection failed\n';
    exit(1);
}
"

# Check critical endpoints
echo "Testing critical endpoints..."

# Register
curl -X POST http://localhost:8085/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username":"smoketest","email":"smoke@test.com","password":"Test123!@#","password_confirmation":"Test123!@#"}' \
  -f || echo "⚠ Register endpoint issue"

# Login
curl -X POST http://localhost:8085/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"test@example.com","password":"Test123!@#"}' \
  -f || echo "⚠ Login endpoint issue"

echo "✅ All tests passed!"
```

**Run before deployment:**

```bash
chmod +x tests/run_production_tests.sh
./tests/run_production_tests.sh
```

#### Load Testing

Use Apache Bench or k6:

```bash
# Install k6
curl https://github.com/grafana/k6/releases/download/v0.45.0/k6-v0.45.0-linux-amd64.tar.gz -L | tar xvz

# Load test script (load_test.js)
import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  stages: [
    { duration: '1m', target: 50 },   // Ramp up to 50 users
    { duration: '3m', target: 50 },   // Stay at 50 users
    { duration: '1m', target: 100 },  // Ramp up to 100 users
    { duration: '3m', target: 100 },  // Stay at 100 users
    { duration: '1m', target: 0 },    // Ramp down to 0 users
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'], // 95% of requests under 500ms
    http_req_failed: ['rate<0.01'],   // Error rate under 1%
  },
};

export default function () {
  // Test health endpoint
  let res = http.get('https://api.yourdomain.com/health');
  check(res, { 'status is 200': (r) => r.status === 200 });

  sleep(1);

  // Test login
  res = http.post('https://api.yourdomain.com/auth/login',
    JSON.stringify({
      username: 'test@example.com',
      password: 'Test123!@#'
    }),
    { headers: { 'Content-Type': 'application/json' } }
  );
  check(res, { 'login successful': (r) => r.status === 200 });

  sleep(1);
}

# Run load test
./k6 run load_test.js
```

**Pre-Production Load Test Checklist:**

- [ ] API handles 100 concurrent users
- [ ] 95th percentile response time < 500ms
- [ ] Error rate < 1%
- [ ] No memory leaks during sustained load
- [ ] Database connection pool sufficient

---

### 11. File Permissions

```bash
# Set proper ownership
sudo chown -R www-data:www-data /var/www/padi-api

# Set proper permissions
find /var/www/padi-api -type d -exec chmod 755 {} \;
find /var/www/padi-api -type f -exec chmod 644 {} \;

# Writable directories
chmod -R 775 storage/
chmod -R 775 storage/cache/
chmod -R 775 storage/logs/

# Protect .env
chmod 600 .env
```

---

### 12. Deployment Automation

**deploy.sh:**

```bash
#!/bin/bash

echo "🚀 Starting deployment..."

# Pull latest code
git pull origin main

# Install dependencies (production only)
composer install --no-dev --optimize-autoloader

# Run migrations
php scripts/migrate.php migrate

# Clear cache (if implemented)
# php scripts/cache.php clear

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Restart Nginx
sudo systemctl restart nginx

echo "✅ Deployment completed!"
```

---

## 🔍 Post-Deployment Verification

### 1. Test Critical Endpoints

```bash
# Health check
curl https://api.yourdomain.com/health

# Register
curl -X POST https://api.yourdomain.com/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","email":"test@example.com","password":"Test123!@#","password_confirmation":"Test123!@#"}'

# Login
curl -X POST https://api.yourdomain.com/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"test@example.com","password":"Test123!@#"}'

# Protected endpoint
curl https://api.yourdomain.com/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 2. Check Logs

```bash
# Check for errors
tail -f /var/log/nginx/api_error.log
tail -f storage/logs/php_error.log

# Monitor access
tail -f /var/log/nginx/api_access.log
```

### 3. Security Scan

```bash
# SSL test
https://www.ssllabs.com/ssltest/analyze.html?d=api.yourdomain.com

# Security headers test
https://securityheaders.com/?q=https://api.yourdomain.com
```

---

## 🚨 Common Production Issues

### Issue 1: 500 Internal Server Error

**Check:**

- PHP error logs
- File permissions
- .env configuration
- Database connection

### Issue 2: CORS Errors

**Fix:**

```bash
# Update CORS_ALLOWED_ORIGINS in .env
CORS_ALLOWED_ORIGINS=https://yourdomain.com
```

### Issue 3: Slow Response Times

**Solutions:**

- Enable OPcache
- Add database indexes
- Use Redis for caching
- Optimize queries

### Issue 4: Rate Limit Too Restrictive

**Adjust:**

```bash
RATE_LIMIT_MAX=100
RATE_LIMIT_WINDOW=60
```

---

## 📊 Monitoring Tools (Recommended)

1. **Server Monitoring:**
   - New Relic
   - Datadog
   - Prometheus + Grafana

2. **Application Monitoring:**
   - Sentry (error tracking)
   - LogRocket
   - Rollbar

3. **Uptime Monitoring:**
   - UptimeRobot
   - Pingdom
   - StatusCake

---

## 🔐 Security Best Practices

1. ✅ Never commit .env to Git
2. ✅ Use different credentials for dev/staging/prod
3. ✅ Regularly update dependencies (`composer update`)
4. ✅ Monitor security advisories
5. ✅ Implement IP whitelisting for admin endpoints
6. ✅ Use prepared statements (already done)
7. ✅ Regular security audits
8. ✅ Keep backups encrypted

---

## 📝 Maintenance Schedule

**Daily:**

- Monitor error logs
- Check health endpoint

**Weekly:**

- Review access logs
- Check disk space
- Verify backups

**Monthly:**

- Update dependencies
- Security patches
- Performance review
- Backup restoration test

**Quarterly:**

- Security audit
- Load testing
- Disaster recovery drill

---

## 🎯 Success Metrics

Monitor these KPIs:

- **Uptime:** Target 99.9%
- **Response Time:** < 200ms (95th percentile)
- **Error Rate:** < 0.1%
- **Failed Logins:** Monitor for brute force attempts
- **Database Queries:** Keep below 5 per request
- **Memory Usage:** Monitor for leaks

---

## 🆘 Emergency Contacts

```bash
# Add your team contacts
DevOps Lead: +1-xxx-xxx-xxxx
Database Admin: +1-xxx-xxx-xxxx
Security Team: security@yourdomain.com
```

---

## ✅ Final Pre-Deployment Checklist

Before going live:

**Environment & Configuration:**

- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] Strong JWT_SECRET (64+ chars)
- [ ] CORS properly configured
- [ ] .env file secured (chmod 600)

**Security:**

- [ ] SSL certificate installed and forced
- [ ] Security headers configured
- [ ] Rate limiting tested (30 req/min)
- [ ] File permissions set correctly
- [ ] Database user has minimal privileges

**Database:**

- [ ] Database backups automated
- [ ] Backup restoration tested
- [ ] Migrations run successfully

**Monitoring & Logging:**

- [ ] Error logging configured
- [ ] Log rotation set up
- [ ] Health check endpoint working
- [ ] Monitoring dashboard configured

**Testing:**

- [ ] All unit tests passing
- [ ] Integration tests passing
- [ ] Load testing completed (100+ concurrent users)
- [ ] All endpoints smoke tested
- [ ] Rate limiting verified

**API Versioning:**

- [ ] Versioning strategy implemented
- [ ] Deprecation policy documented

**Performance:**

- [ ] OPcache enabled
- [ ] Redis cache configured
- [ ] Response time < 200ms (95th percentile)

**Documentation:**

- [ ] API documentation updated
- [ ] Deployment process documented
- [ ] Rollback plan documented
- [ ] Team trained on deployment

**Emergency Preparedness:**

- [ ] Emergency contacts listed
- [ ] Incident response plan ready

---

**Your API is now production-ready! 🚀**

For issues or questions, refer to:

- [Security Guide](../03-advanced/SECURITY.md)
- [Performance Guide](PERFORMANCE.md)
- [Docker Deployment](DOCKER.md)
