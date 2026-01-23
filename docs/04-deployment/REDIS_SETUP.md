# Redis Cache Configuration - Summary

## ✅ Apa yang Sudah Dikonfigurasi

### 1. **Docker Compose** (`docker-compose.yml`)

- ✅ Service `padi_app` - FrankenPHP standard mode
- ✅ Service `padi_worker` - FrankenPHP worker mode (high performance)
- ✅ Service `padi_nginx` - Nginx reverse proxy dengan SSL support
- ✅ Service `redis` - Redis 7 Alpine untuk cache
- ✅ Environment variables untuk Redis (`CACHE_DRIVER=redis`, `REDIS_HOST=redis`)
- ✅ Redis volume untuk persistensi data

### 2. **Core Cache Class** (`core/Cache.php`)

- ✅ Dual driver support: `file` dan `redis`
- ✅ Auto-detect driver dari environment variable `CACHE_DRIVER`
- ✅ Fallback ke file cache jika Redis gagal
- ✅ Support Predis client untuk koneksi Redis
- ✅ Methods: `get()`, `set()`, `has()`, `delete()`, `clear()`, `remember()`

### 3. **Environment Configuration**

- ✅ `.env.example` - Updated dengan penjelasan Redis
- ✅ `.env.docker` - Template untuk Docker deployment dengan Redis

### 4. **Documentation**

- ✅ `DOCKER_DEPLOY.md` - Panduan lengkap deployment Docker dengan Redis
- ✅ `README.md` - Updated dengan info Redis dan Docker
- ✅ `scripts/test_redis.php` - Script untuk test cache configuration

## 🚀 Cara Menggunakan

### Development (File Cache)

```bash
# .env
CACHE_DRIVER=file

php -S localhost:8085 -t public
```

### Production (Redis Cache) - Docker

**Pilih salah satu mode deployment:**

#### Mode 1: Standard (Dev/Low Traffic)

```bash
cp .env.docker .env
# Edit CACHE_DRIVER=redis (sudah default)

docker compose -f docker-compose.standard.yml up -d
docker compose -f docker-compose.standard.yml exec padi_app php scripts/test_redis.php
```

#### Mode 2: Worker (Production - RECOMMENDED)

```bash
cp .env.docker .env
# Edit CACHE_DRIVER=redis (sudah default)

docker compose -f docker-compose.worker.yml up -d
docker compose -f docker-compose.worker.yml exec padi_worker php scripts/test_redis.php
```

#### Mode 3: Full Stack with Nginx

```bash
cp .env.docker .env
# Edit CACHE_DRIVER=redis (sudah default)

docker compose -f docker-compose.nginx.yml up -d
docker compose -f docker-compose.nginx.yml exec padi_app php scripts/test_redis.php
```

### Production (Redis Cache) - Manual

```bash
# Install Redis
# Ubuntu: sudo apt install redis-server
# Mac: brew install redis
# Windows: Download from redis.io

# .env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Test
php scripts/test_redis.php
```

## 📊 Perbandingan Driver

| Feature     | File Cache | Redis Cache    |
| ----------- | ---------- | -------------- |
| Speed       | Fast       | **Ultra Fast** |
| Memory      | Disk       | RAM            |
| Distributed | ❌         | ✅             |
| TTL Support | ✅         | ✅             |
| Setup       | None       | Redis server   |
| Production  | Dev only   | ✅ Recommended |

## 🎯 Mode Deployment

Tersedia 3 mode deployment, Redis **selalu included** di semua mode:

### Mode 1: Standard + Redis

```bash
docker compose -f docker-compose.standard.yml up -d
# Port: 8085
# Use: Development/testing
# Redis: ✅ Always included
```

### Mode 2: Worker + Redis (Recommended)

```bash
docker compose -f docker-compose.worker.yml up -d
# Port: 8085
# Use: Production high-traffic
# Performance: 10-100x faster
# Redis: ✅ Always included
```

### Mode 3: Full Stack (Nginx + Redis)

```bash
docker compose -f docker-compose.nginx.yml up -d
# Port: 80 (HTTP), 443 (HTTPS)
# Includes: padi_app + padi_worker + padi_nginx + redis
# Use: Complete production setup with SSL
# Redis: ✅ Always included
```

## 📝 Environment Variables

```env
# Cache Configuration
CACHE_DRIVER=redis                # file|redis
REDIS_HOST=redis                  # redis (Docker) or 127.0.0.1 (local)
REDIS_PORT=6379
REDIS_PASSWORD=                   # optional
REDIS_DATABASE=0                  # 0-15
```

## 🧪 Testing

```bash
# Test cache configuration
php scripts/test_redis.php

# Docker
docker compose exec padi_app php scripts/test_redis.php

# Monitor Redis
docker compose exec redis redis-cli MONITOR

# View all keys
docker compose exec redis redis-cli KEYS "*"

# Get cache stats
docker compose exec redis redis-cli INFO stats
```

## 🛠️ Commands Cheat Sheet

```bash
# Docker Management (ganti sesuai mode: standard/worker/nginx)
docker compose -f docker-compose.worker.yml up -d       # Start
docker compose -f docker-compose.worker.yml down        # Stop
docker compose -f docker-compose.worker.yml logs -f     # View logs
docker compose -f docker-compose.worker.yml restart     # Restart
docker compose -f docker-compose.worker.yml ps          # Status

# Redis Management
docker compose -f docker-compose.worker.yml exec redis redis-cli ping            # Test
docker compose -f docker-compose.worker.yml exec redis redis-cli FLUSHDB        # Clear cache
docker compose -f docker-compose.worker.yml exec redis redis-cli DBSIZE         # Count keys
docker compose -f docker-compose.worker.yml exec redis redis-cli INFO memory    # Memory

# App Management
docker compose -f docker-compose.worker.yml exec padi_worker php scripts/test_redis.php
docker compose -f docker-compose.worker.yml exec padi_worker php scripts/migrate.php migrate
```

## 📚 Documentation Links

- [DOCKER_DEPLOY.md](DOCKER_DEPLOY.md) - Complete Docker deployment guide
- [README.md](../../README.md) - Main documentation
- [.env.example](../../.env.example) - Environment configuration examples

## ⚡ Quick Start

```bash
# 1. Setup
cp .env.docker .env
# Edit .env sesuai kebutuhan (Redis sudah default!)

# 2. Pilih mode dan start
# RECOMMENDED: Worker mode untuk production
docker compose -f docker-compose.worker.yml up -d

# 3. Test
docker compose -f docker-compose.worker.yml exec padi_worker php scripts/test_redis.php

# 4. Access API
curl http://localhost:8085/
```

## 🎉 Done!

Redis cache sudah dikonfigurasi dan **selalu terinstall** di semua mode deployment! 🚀

**Pilihan deployment:**

- `docker-compose.standard.yml` - Standard + Redis
- `docker-compose.worker.yml` - Worker + Redis ⚡ **RECOMMENDED**
- `docker-compose.nginx.yml` - Full stack with Nginx + Redis
