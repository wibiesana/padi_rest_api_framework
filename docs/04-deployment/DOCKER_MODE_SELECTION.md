# 🐳 Docker Deployment - Mode Selection

## Pilih Mode Deployment Anda

Tersedia 3 mode deployment yang sudah dikonfigurasi. **Redis selalu included di semua mode!**

---

## 1️⃣ Standard Mode (Development/Testing)

**File:** `docker-compose.standard.yml`

### Kapan Digunakan:

- ✅ Development dan testing
- ✅ Low-medium traffic
- ✅ Auto-reload saat code changes
- ✅ Debugging

### Cara Deploy:

```bash
# Setup
cp .env.docker .env
# Edit .env sesuai kebutuhan

# Deploy
docker compose -f docker-compose.standard.yml up -d

# Test
docker compose -f docker-compose.standard.yml exec padi_app php scripts/test_redis.php

# Logs
docker compose -f docker-compose.standard.yml logs -f
```

**Access:** http://localhost:8085

---

## 2️⃣ Worker Mode ⚡ **RECOMMENDED for Production**

**File:** `docker-compose.worker.yml`

### Kapan Digunakan:

- ✅ Production deployment
- ✅ High traffic / high performance
- ✅ **10-100x lebih cepat** dari standard mode
- ✅ Memory efficient

### Cara Deploy:

```bash
# Setup
cp .env.docker .env
# Edit .env untuk production (APP_ENV=production, APP_DEBUG=false)

# Deploy
docker compose -f docker-compose.worker.yml up -d

# Test
docker compose -f docker-compose.worker.yml exec padi_worker php scripts/test_redis.php

# Logs
docker compose -f docker-compose.worker.yml logs -f

# IMPORTANT: Restart after code changes!
docker compose -f docker-compose.worker.yml restart padi_worker
```

**Access:** http://localhost:8085

**Performance:** 🚀 **10-100x faster** than standard mode!

---

## 3️⃣ Full Stack with Nginx (Production + SSL)

**File:** `docker-compose.nginx.yml`

### Kapan Digunakan:

- ✅ Production with SSL/TLS
- ✅ Load balancing
- ✅ Reverse proxy
- ✅ Rate limiting di Nginx level

### Cara Deploy:

```bash
# 1. Setup SSL certificates
mkdir -p docker/nginx/ssl
# Copy your SSL certificates to docker/nginx/ssl/
# - fullchain.pem
# - privkey.pem

# 2. Edit nginx config
# Edit docker/nginx/nginx.conf
# Change server_name to your domain

# 3. Setup environment
cp .env.docker .env
# Edit .env untuk production

# 4. Deploy
docker compose -f docker-compose.nginx.yml up -d

# 5. Test
docker compose -f docker-compose.nginx.yml exec padi_app php scripts/test_redis.php

# Logs
docker compose -f docker-compose.nginx.yml logs -f
```

**Access:**

- HTTP: http://localhost
- HTTPS: https://localhost (requires SSL setup)

---

## 📊 Perbandingan Mode

| Feature         | Standard | Worker                | Nginx Full            |
| --------------- | -------- | --------------------- | --------------------- |
| **Performance** | Normal   | ⚡ **10-100x faster** | ⚡ **10-100x faster** |
| **Redis Cache** | ✅       | ✅                    | ✅                    |
| **SSL/TLS**     | ❌       | ❌                    | ✅                    |
| **Port**        | 8085     | 8085                  | 80, 443               |
| **Auto reload** | ✅       | ❌ (manual restart)   | ❌ (manual restart)   |
| **Memory**      | Low      | Medium                | High                  |
| **Best for**    | Dev/Test | Production            | Production+SSL        |

---

## 🎯 Rekomendasi

### Development:

```bash
docker compose -f docker-compose.standard.yml up -d
```

### Production (No SSL):

```bash
docker compose -f docker-compose.worker.yml up -d
```

### Production (With SSL):

```bash
docker compose -f docker-compose.nginx.yml up -d
```

---

## ⚙️ Redis Configuration

Redis **sudah otomatis dikonfigurasi** di semua mode:

```env
CACHE_DRIVER=redis      # Sudah default!
REDIS_HOST=redis        # Sudah default!
REDIS_PORT=6379         # Sudah default!
```

Anda tidak perlu konfigurasi tambahan! ✅

---

## 🔄 Management Commands

### Stop

```bash
# Sesuaikan dengan mode yang digunakan
docker compose -f docker-compose.worker.yml down
```

### Restart

```bash
docker compose -f docker-compose.worker.yml restart
```

### Rebuild

```bash
docker compose -f docker-compose.worker.yml up -d --build
```

### View Logs

```bash
docker compose -f docker-compose.worker.yml logs -f
```

### Check Status

```bash
docker compose -f docker-compose.worker.yml ps
```

---

## 🧪 Test Redis

```bash
# Standard mode
docker compose -f docker-compose.standard.yml exec padi_app php scripts/test_redis.php

# Worker mode
docker compose -f docker-compose.worker.yml exec padi_worker php scripts/test_redis.php

# Nginx mode
docker compose -f docker-compose.nginx.yml exec padi_app php scripts/test_redis.php
```

---

## 📚 Dokumentasi Lengkap

- **[DOCKER_DEPLOY.md](DOCKER_DEPLOY.md)** - Panduan lengkap deployment
- **[REDIS_SETUP.md](REDIS_SETUP.md)** - Redis configuration guide
- **[README.md](../../README.md)** - Main documentation

---

**Need help?** Check [DOCKER_DEPLOY.md](DOCKER_DEPLOY.md) for troubleshooting!
