# FrankenPHP Mode Configuration

Aplikasi ini mendukung dua mode operasional FrankenPHP:

## Mode yang Tersedia

### 1. **Standard Mode** (Default)

- PHP dijalankan untuk setiap request
- Cocok untuk development dan debugging
- Memory usage lebih rendah
- Performa: ~50-70ms per request

### 2. **Worker Mode** (Production)

- PHP worker tetap di memory
- Bootstrap aplikasi hanya sekali
- Performa tinggi untuk production
- Performa: ~25-35ms per request setelah warm-up

## Cara Menggunakan

### Metode 1: Quick Switch (Tanpa Rebuild)

Cepat, tidak perlu rebuild image:

```powershell
# Switch ke worker mode
.\quick-switch.ps1 worker

# Switch ke standard mode
.\quick-switch.ps1 standard
```

### Metode 2: Full Rebuild

Rebuild image dari awal (lebih aman):

```powershell
# Switch ke worker mode
.\switch-mode.ps1 worker

# Switch ke standard mode
.\switch-mode.ps1 standard
```

### Metode 3: Manual

```powershell
# 1. Copy Caddyfile yang diinginkan
Copy-Item Caddyfile.worker Caddyfile
# atau
Copy-Item Caddyfile.standard Caddyfile

# 2. Restart container
docker-compose restart app
```

## File Konfigurasi

- `Caddyfile.standard` - Konfigurasi standard mode
- `Caddyfile.worker` - Konfigurasi worker mode
- `Caddyfile` - File aktif yang digunakan (di-copy dari salah satu di atas)

## Testing Performa

```powershell
# Single request
curl http://localhost:8085/

# Multiple requests untuk test performa
1..10 | ForEach-Object {
    Measure-Command { curl.exe -s http://localhost:8085/ | Out-Null } |
    Select-Object -ExpandProperty TotalMilliseconds
}
```

## Rekomendasi

- **Development**: Gunakan **standard mode** untuk debugging yang lebih mudah
- **Production**: Gunakan **worker mode** untuk performa maksimal
- **Testing**: Gunakan **standard mode** untuk test yang lebih konsisten

## Troubleshooting

Jika ada masalah setelah switch mode:

```powershell
# Reset dan rebuild
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```
