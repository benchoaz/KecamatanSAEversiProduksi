#!/usr/bin/env powershell
# Docker Optimization Monitoring Script

Write-Host "=====================================" -ForegroundColor Green
Write-Host "Docker Optimization Status Monitor" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host ""

# Check Docker daemon
Write-Host "[1] Docker Daemon Status:" -ForegroundColor Cyan
docker ps > $null 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Docker daemon running" -ForegroundColor Green
} else {
    Write-Host "✗ Docker daemon not responding" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Check Images
Write-Host "[2] Docker Images:" -ForegroundColor Cyan
docker images | Format-Table

Write-Host ""

# Check Containers
Write-Host "[3] Running Containers:" -ForegroundColor Cyan
docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}" 

Write-Host ""

# Check Disk Space
Write-Host "[4] Docker Disk Usage:" -ForegroundColor Cyan
docker system df

Write-Host ""

# Check Build Status
Write-Host "[5] Image Sizes (optimized):" -ForegroundColor Cyan
docker images --filter "reference=dashboard-kecamatan-app*" --format "table {{.Repository}}\t{{.Tag}}\t{{.Size}}"

Write-Host ""

# Network Status
Write-Host "[6] Networks:" -ForegroundColor Cyan
docker network ls --filter "name=app-network"

Write-Host ""
Write-Host "=====================================" -ForegroundColor Green
Write-Host "Optimization complete!" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
