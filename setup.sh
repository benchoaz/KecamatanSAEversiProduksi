#!/bin/bash
set -e

# ============================================================
# SILAP System Setup & Deployment Script
# =) Centralized Installer for Kecamatan Besuk
# ============================================================

echo "🏛️  SILAP System - Setup Wizard"
echo "--------------------------------"

# 1. Check Prerequisities
echo "🔍 Checking prerequisites..."
if ! command -v docker &> /dev/null; then
    echo "❌ Error: Docker is not installed. Please install Docker first."
    exit 1
fi

# 2. Setup Environment
echo "📝 Preparing environment files..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ Root .env created from example. (Please edit it later)"
else
    echo "ℹ️  Root .env already exists."
fi

if [ ! -f app/.env ]; then
    cp app/.env.example app/.env
    echo "✅ App .env created from example."
else
    echo "ℹ️  App .env already exists."
fi

# 3. Pull & Build
echo "🏗️  Building system components..."
docker compose build --pull

# 4. Start System
echo "🚀 Starting services..."
docker compose up -d

# 5. Initialize App
echo "⚙️  Initializing Laravel Core..."
docker exec kecamatan-app php artisan key:generate --ansi
docker exec kecamatan-app php artisan optimize:clear

echo "--------------------------------"
echo "✅ SETUP COMPLETE!"
echo "🌐 Your system is running on port 80/443."
echo "👉 Use 'docker compose logs -f' to monitor the system."
