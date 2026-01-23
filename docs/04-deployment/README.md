# Deployment Documentation

Dokumentasi lengkap untuk deployment dan konfigurasi production Padi REST API.

## 📚 Daftar Dokumentasi

### FrankenPHP & Performance

- **[MODE_SWITCHING.md](MODE_SWITCHING.md)** - Cara switch antara Worker Mode dan Standard Mode
- **[PERFORMANCE.md](PERFORMANCE.md)** - Benchmark dan comparison performa kedua mode
- **[FRANKENPHP_SETUP.md](FRANKENPHP_SETUP.md)** - Setup lengkap FrankenPHP
- **[FRANKENPHP_IMPLEMENTATION.md](FRANKENPHP_IMPLEMENTATION.md)** - Detail implementasi FrankenPHP

### Deployment

- **[DOCKER.md](DOCKER.md)** - Docker deployment guide
- **[PRODUCTION.md](PRODUCTION.md)** - Production best practices
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Troubleshooting guide

## 🚀 Quick Start

### Switch FrankenPHP Mode

```powershell
# Worker Mode (Production - High Performance)
.\quick-switch.ps1 worker

# Standard Mode (Development - Easy Debugging)
.\quick-switch.ps1 standard
```

### Performance Comparison

| Mode     | Cold Start | Warm Requests | Best For    |
| -------- | ---------- | ------------- | ----------- |
| Worker   | ~40ms      | ~28ms         | Production  |
| Standard | ~110ms     | ~30ms         | Development |

## 📖 Dokumentasi Terkait

- [Getting Started](../01-getting-started/) - Setup awal aplikasi
- [Core Concepts](../02-core-concepts/) - Konsep dasar framework
- [Advanced](../03-advanced/) - Fitur advanced
- [Examples](../05-examples/) - Contoh implementasi

## 🔗 Quick Links

- [Main README](../../README.md)
- [Documentation Index](../INDEX.md)
- [Docker Compose](../../docker-compose.yml)
- [Caddyfile](../../Caddyfile)
