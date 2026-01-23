# FrankenPHP Mode Switcher
# Usage: .\switch-mode.ps1 [worker|standard]

param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("worker", "standard")]
    [string]$Mode
)

$caddyfileSource = "Caddyfile.$Mode"
$caddyfileDest = "Caddyfile"

if (-Not (Test-Path $caddyfileSource)) {
    Write-Host "Error: File $caddyfileSource not found!" -ForegroundColor Red
    exit 1
}

Write-Host "Switching to $Mode mode..." -ForegroundColor Cyan

# Copy the selected Caddyfile
Copy-Item $caddyfileSource $caddyfileDest -Force

Write-Host "Caddyfile updated" -ForegroundColor Green

# Rebuild and restart container
Write-Host "Rebuilding and restarting container..." -ForegroundColor Cyan
docker-compose down
docker-compose build --no-cache app
docker-compose up -d

Write-Host ""
Write-Host "Container restarted in $Mode mode!" -ForegroundColor Green
Write-Host "Test with: curl http://localhost:8085/" -ForegroundColor Yellow
Write-Host "Docs: docs/04-deployment/MODE_SWITCHING.md" -ForegroundColor Gray
