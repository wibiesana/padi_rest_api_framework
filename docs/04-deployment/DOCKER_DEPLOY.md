# Docker Deployment dengan Redis

## 📦 Arsitektur

Tersedia 3 mode deployment yang sudah dikonfigurasi:

### 1. **Standard Mode** (`docker-compose.standard.yml`)

- FrankenPHP standard HTTP mode
- Redis cache (always included)
- Cocok untuk: Development, Low-Medium traffic

### 2. **Worker Mode** (`docker-compose.worker.yml`) ⚡ **RECOMMENDED**

- FrankenPHP worker mode (10-100x lebih cepat)
- Redis cache (always included)
- Cocok untuk: Production, High traffic

### 3. **Full Stack with Nginx** (`docker-compose.nginx.yml`)

- FrankenPHP (Standard + Worker)
- Nginx reverse proxy dengan SSL
- Redis cache (always included)
- Cocok untuk: Production with SSL/TLS

**Redis selalu terinstall** di semua mode deployment! 🎯

---

## 🚀 Quick Start

### Pilih Mode Deployment Anda:

### Pilih Mode Deployment Anda:

#### 🔹 Mode 1: Standard (Development/Testing)

```bash
docker compose -f docker-compose.standard.yml up -d
```

**Includes:** FrankenPHP Standard + Redis  
**Port:** 8085  
**Use:** Development, testing, low-medium traffic

#### ⚡ Mode 2: Worker (Production - RECOMMENDED)

```bash
docker compose -f docker-compose.worker.yml up -d
```

**Includes:** FrankenPHP Worker + Redis  
**Port:** 8085  
**Use:** Production, high traffic  
**Performance:** 10-100x faster than standard mode

#### 🔒 Mode 3: Full Stack with Nginx (Production with SSL)

```bash
docker compose -f docker-compose.nginx.yml up -d
```

**Includes:** FrankenPHP (Standard + Worker) + Nginx + Redis  
**Port:** 80 (HTTP), 443 (HTTPS)  
**Use:** Production with SSL/TLS, load balancing

---

### 1. Setup Environment

```bash
# Copy dan edit file env
cp .env.docker .env

# Edit .env dan sesuaikan:
# - DB credentials
# - JWT_SECRET (generate: openssl rand -hex 32)
# - APP_URL
# - CORS_ALLOWED_ORIGINS
# Redis sudah otomatis menggunakan CACHE_DRIVER=redis
```

### 2. Build dan Deploy

```bash
# Pilih salah satu mode:

# Standard Mode
docker compose -f docker-compose.standard.yml build
docker compose -f docker-compose.standard.yml up -d

# Worker Mode (RECOMMENDED)
docker compose -f docker-compose.worker.yml build
docker compose -f docker-compose.worker.yml up -d

# Full Stack with Nginx
docker compose -f docker-compose.nginx.yml build
docker compose -f docker-compose.nginx.yml up -d
```

### 3. Cek Status

```bash
# Lihat logs (sesuaikan dengan mode yang dipilih)
docker compose -f docker-compose.standard.yml logs -f
docker compose -f docker-compose.worker.yml logs -f
docker compose -f docker-compose.nginx.yml logs -f

# Cek service yang running
docker compose -f docker-compose.worker.yml ps

# Test Redis connection (semua mode punya Redis)
docker compose -f docker-compose.worker.yml exec redis redis-cli ping
# Expected: PONG
```

---

## 📋 Perbandingan Mode Deployment

| Feature                | Standard | Worker             | Nginx Full         |
| ---------------------- | -------- | ------------------ | ------------------ |
| Performance            | Normal   | **10-100x faster** | **10-100x faster** |
| Redis Cache            | ✅       | ✅                 | ✅                 |
| SSL/TLS                | ❌       | ❌                 | ✅                 |
| Port                   | 8085     | 8085               | 80, 443            |
| Restart on code change | Auto     | Manual restart     | Manual restart     |
| Memory                 | Low      | Medium             | High               |
| Best for               | Dev/Test | Production         | Production+SSL     |

---

## 🔧 Configuration

### Redis - Sudah Default!

Redis **selalu terinstall** dan **sudah dikonfigurasi sebagai default** di semua mode:

```env
# Sudah otomatis di semua mode
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

Anda tidak perlu konfigurasi tambahan! Redis langsung berfungsi. ✅

### Redis Usage

Redis digunakan untuk:

- **Cache** - Query results, computed data
- **Session** - User sessions (future)
- **Rate Limiting** - API rate limits (future)

### Test Redis Cache

```bash
# Masuk ke container (pilih sesuai mode yang digunakan)
# Standard mode:
docker compose -f docker-compose.standard.yml exec padi_app php scripts/test_redis.php

# Worker mode:
docker compose -f docker-compose.worker.yml exec padi_worker php scripts/test_redis.php

# Nginx mode:
docker compose -f docker-compose.nginx.yml exec padi_app php scripts/test_redis.php
```

### Monitor Redis

```bash
# Lihat semua keys (sesuaikan dengan mode)
docker compose -f docker-compose.worker.yml exec redis redis-cli KEYS '*'

# Monitor real-time commands
docker compose -f docker-compose.worker.yml exec redis redis-cli MONITOR

# Get cache statistics
docker compose -f docker-compose.worker.yml exec redis redis-cli INFO stats
```

---

## 📊 Service Ports

| Mode     | Service     | Port    | URL                   |
| -------- | ----------- | ------- | --------------------- |
| Standard | padi_app    | 8085    | http://localhost:8085 |
| Standard | redis       | 6379    | localhost:6379        |
| Worker   | padi_worker | 8085    | http://localhost:8085 |
| Worker   | redis       | 6379    | localhost:6379        |
| Nginx    | padi_nginx  | 80, 443 | http://localhost      |
| Nginx    | redis       | -       | (internal only)       |

---

## 🔄 Management Commands

### Standard Mode

```bash
# Stop
docker compose -f docker-compose.standard.yml down

# Restart
docker compose -f docker-compose.standard.yml restart

# Rebuild
docker compose -f docker-compose.standard.yml up -d --build

# Logs
docker compose -f docker-compose.standard.yml logs -f
```

### Worker Mode

```bash
# Stop
docker compose -f docker-compose.worker.yml down

# Restart (perlu setelah code changes!)
docker compose -f docker-compose.worker.yml restart padi_worker

# Rebuild
docker compose -f docker-compose.worker.yml up -d --build

# Logs
docker compose -f docker-compose.worker.yml logs -f
```

### Nginx Full Stack

```bash
# Stop
docker compose -f docker-compose.nginx.yml down

# Restart
docker compose -f docker-compose.nginx.yml restart

# Rebuild
docker compose -f docker-compose.nginx.yml up -d --build

# Logs
docker compose -f docker-compose.nginx.yml logs -f
```

# Lihat resource usage

docker compose stats

````

### Logs

```bash
# All services
docker compose logs -f

# Specific service
docker compose logs -f padi_app
docker compose logs -f redis

# Last 100 lines
docker compose logs --tail=100 padi_app
````

### Database Migration

```bash
# Run migrations
docker compose exec padi_app php scripts/migrate.php

# Generate model
docker compose exec padi_app php scripts/generate.php model products
```

## 🛡️ Production Deployment

### 1. Setup SSL Certificate

```bash
# Letakkan certificate di docker/nginx/ssl/
docker/nginx/ssl/
├── fullchain.pem
└── privkey.pem
```

### 2. Update Nginx Config

Edit `docker/nginx/nginx.conf`:

```nginx
server_name api.yourdomain.com;  # Ganti domain
```

### 3. Security Checklist

- ✅ Set `APP_ENV=production`
- ✅ Set `APP_DEBUG=false`
- ✅ Generate strong `JWT_SECRET`
- ✅ Setup SSL certificates
- ✅ Configure CORS properly
- ✅ Use strong database passwords
- ✅ Enable firewall rules
- ✅ Setup Redis password (optional):
  ```yaml
  environment:
    - REDIS_PASSWORD=your-redis-password
  ```

### 4. Deploy

```bash
# Production build
docker compose -f docker-compose.yml build --no-cache

# Start with worker mode for best performance
docker compose up -d padi_worker padi_nginx redis

# Monitor
docker compose logs -f
```

## 🐛 Troubleshooting

### Redis Connection Failed

```bash
# Check Redis is running
docker compose ps redis

# Check logs
docker compose logs redis

# Test connection
docker compose exec redis redis-cli ping
```

**Fix:** Pastikan service `redis` running dan `REDIS_HOST=redis` di `.env`

### App Can't Connect to MySQL

```bash
# Check host.docker.internal works
docker compose exec padi_app ping -c 2 host.docker.internal
```

**Fix:** Pastikan MySQL running di host dan accessible

### Worker Mode Not Updating Code

Worker mode cache kode di memory. Setelah ubah kode:

```bash
# Restart worker
docker compose restart padi_worker
```

### Permission Errors

```bash
# Fix storage permissions
docker compose exec padi_app chmod -R 775 storage
docker compose exec padi_app chown -R www-data:www-data storage
```

## 📈 Performance Tips

1. **Use Worker Mode** - 10-100x faster
2. **Enable Redis** - Set `CACHE_DRIVER=redis`
3. **Enable OPcache** - Already enabled in FrankenPHP
4. **Use Nginx** - Better handling static files & SSL
5. **Set proper cache TTL** - Balance freshness vs performance

## 🔗 Links

- [FrankenPHP Docs](https://frankenphp.dev/)
- [Redis Docs](https://redis.io/docs/)
- [Nginx Docs](https://nginx.org/en/docs/)
- [Docker Compose Docs](https://docs.docker.com/compose/)
