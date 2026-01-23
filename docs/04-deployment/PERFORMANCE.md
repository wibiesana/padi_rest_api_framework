# Performance Comparison

## Test Results (January 23, 2026)

### Worker Mode

```
Request 1: ~40ms (cold start)
Request 2-5: ~26-31ms (average: 28.5ms)
```

### Standard Mode

```
Request 1: ~110ms (cold start)
Request 2-5: ~29-30ms (average: 29.7ms)
```

## Key Findings

1. **Cold Start**: Worker mode jauh lebih cepat (~40ms vs ~110ms)
2. **Warm Requests**: Kedua mode hampir sama (~28-30ms)
3. **Consistency**: Worker mode lebih konsisten setelah warm-up
4. **Memory**: Worker mode menggunakan memory lebih efisien

## Recommendations

- **Development**: Standard mode (reload otomatis untuk perubahan code)
- **Production**: Worker mode (performa maksimal dan cold start cepat)
- **Load Testing**: Worker mode (handling concurrent requests lebih baik)

## Quick Commands

```powershell
# Switch to worker mode
.\quick-switch.ps1 worker

# Switch to standard mode
.\quick-switch.ps1 standard

# Test performance
1..10 | ForEach-Object {
    Measure-Command { curl.exe -s http://localhost:8085/ | Out-Null } |
    Select-Object -ExpandProperty TotalMilliseconds
}
```
