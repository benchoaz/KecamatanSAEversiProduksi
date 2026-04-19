#!/bin/sh
set -e

# ============================================================
# Laravel Production Entrypoint
# ============================================================

echo "🚀 Starting Deployment Automations..."

# Ensure storage directories exist
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# Discover packages (generates packages.php without dev deps)
echo "📦 Discovering packages..."
php artisan package:discover --ansi

# Clear and Cache configuration
echo "⚙️ Optimizing configuration..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations automatically
echo "🗄️ Running database migrations..."
php artisan migrate --force || echo "⚠️ Migration skipped (DB may not be ready yet)"

# Start the application
echo "🏁 SILAP Application is ready!"
exec "$@"
