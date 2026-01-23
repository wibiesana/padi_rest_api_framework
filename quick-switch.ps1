# Quick Mode Switcher (No Rebuild)
# Usage: .\quick-switch.ps1 [worker|standard]

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

# Copy to container and restart
Write-Host "Updating container configuration..." -ForegroundColor Cyan
docker cp $caddyfileDest padi_api:/etc/caddy/Caddyfile
docker-compose restart app

Start-Sleep -Seconds 3

Write-Host ""
Write-Host "Container restarted in $Mode mode!" -ForegroundColor Green
Write-Host "Test with: curl http://localhost:8085/" -ForegroundColor Yellow
Write-Host "Docs: docs/04-deployment/MODE_SWITCHING.md" -ForegroundColor Gray
