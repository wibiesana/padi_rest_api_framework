# 🐳 Docker Setup Options

Ada 3 pilihan docker-compose yang tersedia:

---

## 📋 Pilihan Docker Compose

### 1. **docker-compose.yml** (Standalone - Recommended)

**Untuk:** Development dengan MySQL eksternal (XAMPP, MySQL lokal, dll)

**Services:**

- ✅ FrankenPHP only
- ❌ MySQL (gunakan MySQL lokal Anda)

**Cara Pakai:**

```bash
# Start FrankenPHP saja
docker-compose up -d

# Stop
docker-compose down
```

**Database Connection:**

- Host: `host.docker.internal` (otomatis ke MySQL lokal)
- Port: `3306`
- Database: `rest_api_db`
- User: `root`
- Password: (sesuai MySQL lokal Anda)

**Update `.env` atau environment di docker-compose.yml:**

```env
DB_HOST=host.docker.internal
DB_PORT=3306
DB_NAME=rest_api_db
DB_USER=root
DB_PASS=your_local_mysql_password
```

---

### 2. **docker-compose.full.yml** (Full Stack)

**Untuk:** Development dengan semua services di Docker

**Services:**

- ✅ FrankenPHP
- ✅ MySQL 8.0
- ✅ phpMyAdmin

**Cara Pakai:**

```bash
# Start semua services
docker-compose -f docker-compose.full.yml up -d

# Stop
docker-compose -f docker-compose.full.yml down

# Stop dan hapus data (WARNING: hapus database!)
docker-compose -f docker-compose.full.yml down -v
```

**Access:**

- API: http://localhost:8085
- phpMyAdmin: http://localhost:8080

---

### 3. **docker-compose.prod.yml** (Production)

**Untuk:** Production deployment dengan NGINX

**Services:**

- ✅ FrankenPHP (production build)
- ✅ MySQL
- ✅ NGINX reverse proxy

**Cara Pakai:**

```bash
# Setup environment
cp .env.docker .env.production
# Edit .env.production

# Build dan deploy
docker-compose -f docker-compose.prod.yml --env-file .env.production up -d
```

---

## 🚀 Quick Start

### Scenario 1: Sudah Punya MySQL Lokal (XAMPP, dll)

```bash
# 1. Pastikan MySQL lokal sudah running
# 2. Update DB_PASS di docker-compose.yml sesuai password MySQL lokal
# 3. Start FrankenPHP
docker-compose up -d

# 4. Check logs
docker-compose logs -f

# 5. Test API
curl http://localhost:8085/
```

### Scenario 2: Ingin Semua di Docker

```bash
# 1. Start full stack
docker-compose -f docker-compose.full.yml up -d

# 2. Check status
docker-compose -f docker-compose.full.yml ps

# 3. Access phpMyAdmin
# Browser: http://localhost:8080
# Server: mysql
# User: root
# Password: root_password

# 4. Test API
curl http://localhost:8085/
```

---

## 🔧 Troubleshooting

### Error: Cannot connect to MySQL

**Jika menggunakan docker-compose.yml (standalone):**

1. Pastikan MySQL lokal running
2. Update password di docker-compose.yml:
   ```yaml
   - DB_PASS=your_actual_password
   ```
3. Restart container:
   ```bash
   docker-compose restart
   ```

**Jika menggunakan docker-compose.full.yml:**

1. Check MySQL container:
   ```bash
   docker-compose -f docker-compose.full.yml logs mysql
   ```
2. Wait for MySQL to be ready (30 seconds)

### Error: Port already in use

**Port 8000 sudah dipakai:**

```yaml
# Edit docker-compose.yml
ports:
  - "8001:8000" # Ganti 8000 ke 8001
```

**Port 3306 sudah dipakai (full stack):**

```yaml
# Edit docker-compose.full.yml
mysql:
  ports:
    - "3307:3306" # Ganti 3306 ke 3307
```

---

## 📊 Perbandingan

| Feature    | Standalone        | Full Stack       | Production |
| ---------- | ----------------- | ---------------- | ---------- |
| FrankenPHP | ✅                | ✅               | ✅         |
| MySQL      | ❌ (eksternal)    | ✅               | ✅         |
| phpMyAdmin | ❌                | ✅               | ❌         |
| NGINX      | ❌                | ❌               | ✅         |
| Hot Reload | ✅                | ✅               | ❌         |
| Best For   | Dev (MySQL lokal) | Dev (all-in-one) | Production |

---

## 💡 Rekomendasi

- **Development (sudah punya MySQL):** `docker-compose.yml`
- **Development (fresh start):** `docker-compose.full.yml`
- **Production:** `docker-compose.prod.yml`

---

## 📚 Dokumentasi Lengkap

Lihat [docs/04-deployment/DOCKER.md](docs/04-deployment/DOCKER.md) untuk:

- Panduan lengkap
- Production deployment
- Backup & restore
- Scaling
- Security best practices
