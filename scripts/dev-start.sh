#!/bin/bash
# ============================================================
# SILAP — Local Development Starter Script
# Memastikan sistem jalan dengan benar di localhost (WSL2/Linux)
# ============================================================

set -e

echo "🚀 Memulai SILAP dalam mode LOKAL..."
echo "-------------------------------------"

# 1. Cek .env
if [ ! -f .env ] || [ ! -f app/.env ]; then
    echo "⚠️  File .env tidak ditemukan. Menyiapkan dari example..."
    [ ! -f .env ] && cp .env.example .env
    [ ! -f app/.env ] && cp app/.env.example app/.env
fi

# 2. Jalankan Docker Compose dengan file local override
echo "📦 Menjalankan containers (Core + Automation)..."
docker compose -f docker-compose.yml -f docker-compose.local.yml --profile automation up -d --remove-orphans

# 3. Sinkronisasi Database & Cache
echo "⚙️  Optimasi Laravel..."
docker exec kecamatan-app php artisan optimize:clear
docker exec kecamatan-app php artisan migrate --force

echo "-------------------------------------"
echo "✅ SISTEM SIAP DI LOCALHOST!"
echo ""
echo "🌐 Akses di:"
echo "   - Landing Page : https://localhost"
echo "   - Admin Panel  : https://localhost/admin"
echo "   - n8n Editor   : https://localhost/n8n/ (Login: admin / admin123)"
echo "   - WAHA Dash    : https://localhost/waha/dashboard (Login: admin / admin123)"
echo "   - Traefik Dash : http://localhost:8080"
echo ""
echo "💡 Gunakan './scripts/check-status.sh' untuk cek kesehatan sistem."
