#!/bin/bash
set -e

# ============================================================
# KecamatanSAE - Local Development Setup Script
# ============================================================

echo "🚀 Starting KecamatanSAE Local Setup..."
echo "----------------------------------------"

# 1. Create External Volumes if they don't exist
echo "📦 Checking Docker volumes..."
volumes=("kecamatansaeversikabupaten_pgdata" "kecamatansaeversikabupaten_redis_data")
for vol in "${volumes[@]}"; do
    if ! docker volume inspect "$vol" >/dev/null 2>&1; then
        echo "✅ Creating volume: $vol"
        docker volume create "$vol"
    else
        echo "ℹ️  Volume $vol already exists."
    fi
done

# 2. Environment Setup
echo "📝 Checking environment files..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ .env created from .env.example"
fi

if [ ! -f app/.env ]; then
    cp app/.env.example app/.env
    echo "✅ app/.env created from app/.env.example"
fi

# 3. Docker Build & Up
echo "🏗️  Building and starting containers..."
docker compose -f docker-compose.yml -f docker-compose.local.yml up -d --build

echo "⏳ Waiting for database to be ready..."
sleep 10

# 4. Laravel Initialization
echo "⚙️  Initializing Laravel..."

# Fix storage permissions
docker compose -f docker-compose.yml -f docker-compose.local.yml exec -u root app chown -R www-data:www-data storage bootstrap/cache
docker compose -f docker-compose.yml -f docker-compose.local.yml exec -u root app chmod -R 775 storage bootstrap/cache

# Generate key if not set
if ! grep -q "APP_KEY=base64" app/.env; then
    echo "🔑 Generating APP_KEY..."
    docker compose -f docker-compose.yml -f docker-compose.local.yml exec app php artisan key:generate
fi

# Run migrations
echo "🗄️  Running database migrations..."
docker compose -f docker-compose.yml -f docker-compose.local.yml exec app php artisan migrate --force

# Link storage
echo "🔗 Creating storage link..."
docker compose -f docker-compose.yml -f docker-compose.local.yml exec app php artisan storage:link || true

# Clear cache
echo "🧹 Clearing application cache..."
docker compose -f docker-compose.yml -f docker-compose.local.yml exec app php artisan optimize:clear

echo "----------------------------------------"
echo "✅ SETUP COMPLETE!"
echo "🌐 App is running at: http://localhost:8000"
echo "📧 Admin Dashboard: http://localhost:8000/kecamatan/login"
echo "----------------------------------------"
echo "💡 Tip: Use 'docker compose -f docker-compose.yml -f docker-compose.local.yml logs -f app' to see logs."
