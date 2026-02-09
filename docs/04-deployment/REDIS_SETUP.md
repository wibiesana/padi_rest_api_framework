# Redis Cache Configuration - Summary

## ✅ What Has Been Configured

### 1. **Docker Compose**

- ✅ Service `padi_app` - FrankenPHP standard mode
- ✅ Service `padi_worker` - FrankenPHP worker mode (high performance)
- ✅ Service `padi_nginx` - Nginx reverse proxy with SSL support
- ✅ Service `redis` - Redis 7 Alpine for cache
- ✅ Environment variables for Redis (`CACHE_DRIVER=redis`, `REDIS_HOST=redis`)
- ✅ Redis volume for data persistence

### 2. **Core Cache Class** (`core/Cache.php`)

- ✅ Dual driver support: `file` and `redis`
- ✅ Auto-detect driver from environment variable `CACHE_DRIVER`
- ✅ Fallback to file cache if Redis fails
- ✅ Support for Predis client for Redis connection
- ✅ Methods: `get()`, `set()`, `has()`, `delete()`, `clear()`, `remember()`

### 3. **Environment Configuration**

- ✅ `.env.example` - Updated with Redis explanations
- ✅ `.env.docker` - Template for Docker deployment with Redis

### 4. **Documentation**

- ✅ `DOCKER.md` - Comprehensive Docker deployment guide with Redis
- ✅ `README.md` - Updated with Redis and Docker info
- ✅ `scripts/test_redis.php` - Script to test cache configuration

---

## 🚀 How to Use

### Development (File Cache)

```bash
# .env
CACHE_DRIVER=file

php -S localhost:8085 -t public
```

### Production (Redis Cache) - Docker

**Choose one of the deployment modes:**

#### Mode 1: Standard (Dev/Low Traffic)

```bash
cp .env.docker .env
# Edit CACHE_DRIVER=redis (already default)

docker compose -f docker-compose.standard.yml up -d
docker compose -f docker-compose.standard.yml exec padi_app php scripts/test_redis.php
```

#### Mode 2: Worker (Production - RECOMMENDED)

```bash
cp .env.docker .env
# Edit CACHE_DRIVER=redis (already default)

docker compose -f docker-compose.worker.yml up -d
docker compose -f docker-compose.worker.yml exec padi_worker php scripts/test_redis.php
```

#### Mode 3: Full Stack with Nginx

```bash
cp .env.docker .env
# Edit CACHE_DRIVER=redis (already default)

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

---

## 📊 Driver Comparison

| Feature     | File Cache | Redis Cache    |
| ----------- | ---------- | -------------- |
| Speed       | Fast       | **Ultra Fast** |
| Memory      | Disk       | RAM            |
| Distributed | ❌         | ✅             |
| TTL Support | ✅         | ✅             |
| Setup       | None       | Redis server   |
| Production  | Dev only   | ✅ Recommended |

---

## 🎯 Deployment Modes

3 deployment modes are available, Redis is **always included** in all modes:

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

---

## 📝 Environment Variables

```env
# Cache Configuration
CACHE_DRIVER=redis                # file|redis
REDIS_HOST=redis                  # redis (Docker) or 127.0.0.1 (local)
REDIS_PORT=6379
REDIS_PASSWORD=                   # optional
REDIS_DATABASE=0                  # 0-15
```

---

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

---

## 🛠️ Commands Cheat Sheet

```bash
# Docker Management (replace based on mode: standard/worker/nginx)
docker compose -f docker-compose.worker.yml up -d       # Start
docker compose -f docker-compose.worker.yml down        # Stop
docker compose -f docker-compose.worker.yml logs -f     # View logs
docker compose -f docker-compose.worker.yml restart     # Restart
docker compose -f docker-compose.worker.yml ps          # Status

# Redis Management
docker compose -f docker-compose.worker.yml exec redis redis-cli ping            # Test
docker compose -f docker-compose.worker.yml exec redis redis-cli FLUSHDB        # Clear cache
docker compose -f docker-compose.worker.yml exec redis redis-cli DBSIZE         # Count keys
docker compose -f docker-compose.worker.yml exec redis redis-cli INFO memory    # Memory usage

# App Management
docker compose -f docker-compose.worker.yml exec padi_worker php scripts/test_redis.php
docker compose -f docker-compose.worker.yml exec padi_worker php scripts/migrate.php migrate
```

---

## 📚 Documentation Links

- [DOCKER.md](DOCKER.md) - Complete Docker deployment guide
- [README.md](../../README.md) - Main documentation
- [.env.example](../../.env.example) - Environment configuration examples

---

## 🚀 Quick Start

```bash
# 1. Setup
cp .env.docker .env
# Edit .env as needed (Redis is already default!)

# 2. Choose mode and start
# RECOMMENDED: Worker mode for production
docker compose -f docker-compose.worker.yml up -d

# 3. Test
docker compose -f docker-compose.worker.yml exec padi_worker php scripts/test_redis.php

# 4. Access API
curl http://localhost:8085/
```

---

## 🎉 Done!

Redis cache is configured and **always installed** in all deployment modes! 🚀

**Deployment options:**

- `docker-compose.standard.yml` - Standard + Redis
- `docker-compose.worker.yml` - Worker + Redis ⚡ **RECOMMENDED**
- `docker-compose.nginx.yml` - Full stack with Nginx + Redis
