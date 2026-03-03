#!/bin/bash
# Quick start script for Cloud Shell - copy and paste this entire block into Cloud Shell terminal

set -e

echo "🚀 Dashboard Kecamatan - Cloud Shell Quick Start"
echo "=================================================="
echo ""

# Step 1: Clone repo
echo "📥 Cloning repository..."
cd ~
git clone https://github.com/YOUR_USERNAME/dashboard-kecamatan.git || (cd dashboard-kecamatan && git pull)
cd dashboard-kecamatan

# Step 2: Setup environment
echo "⚙️  Setting up environment..."
[ ! -f .env ] && cp .env.example .env 2>/dev/null || true

# Step 3: Start services
echo "🐳 Starting Docker services..."
echo "(This takes 3-5 minutes on first run)"
docker-compose up -d 2>&1 | grep -E "(Creating|Starting|Already)"

# Step 4: Wait for services
echo "⏳ Waiting for services to be ready..."
sleep 10

for i in {1..30}; do
  if docker-compose exec -T db mysqladmin ping -u root -p$(grep DB_PASSWORD .env | cut -d= -f2) &>/dev/null 2>&1; then
    echo "✅ Database ready!"
    break
  fi
  echo "  Waiting... ($i/30)"
  sleep 2
done

# Step 5: Run migrations
echo "🔄 Running database migrations..."
docker-compose exec -T app php artisan config:cache 2>/dev/null || true
docker-compose exec -T app php artisan migrate --force 2>/dev/null || true

# Step 6: Show status
echo ""
echo "✅ Deployment complete!"
echo ""
echo "📱 Access your services:"
echo "  • Main App:  http://localhost:8000"
echo "  • n8n:       http://localhost:5679"
echo "  • WAHA:      http://localhost:3000"
echo ""
echo "🔗 To use Web Preview in Cloud Shell:"
echo "  1. Click the Web Preview button (top-right of editor)"
echo "  2. Select 'Preview on port 8000'"
echo ""
echo "📋 Useful commands:"
echo "  docker-compose ps          # See all services"
echo "  docker-compose logs -f     # View logs in real-time"
echo "  docker-compose restart     # Restart services"
echo ""
