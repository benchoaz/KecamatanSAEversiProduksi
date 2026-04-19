#!/bin/sh
# Entrypoint script untuk fix permissions dan jalankan PHP-FPM

set -e

# Fix permissions untuk storage dan bootstrap
# Container runs as root initially
if [ "$(id -u)" = "0" ]; then
    echo "🔧 Fixing permissions..."
    
    # Ensure directories exist
    mkdir -p /var/www/storage/framework/sessions \
        /var/www/storage/framework/views \
        /var/www/storage/framework/cache \
        /var/www/storage/logs \
        /var/www/bootstrap/cache
    
    # Fix ownership - CRITICAL for Laravel
    chown -R www-data:www-data /var/www/storage
    chown -R www-data:www-data /var/www/bootstrap
    
    # Fix directory permissions
    find /var/www/storage -type d -exec chmod 775 {} \; 2>/dev/null || true
    find /var/www/bootstrap -type d -exec chmod 775 {} \; 2>/dev/null || true
    
    # Fix file permissions
    find /var/www/storage -type f -exec chmod 664 {} \; 2>/dev/null || true
    find /var/www/bootstrap -type f -exec chmod 664 {} \; 2>/dev/null || true
    
    echo "✅ Permissions fixed"
    
    # Run php-fpm as root - it will spawn workers as www-data based on pool config
    # This is the standard Docker approach for PHP-FPM
    exec "$@"
else
    # Already running as non-root user
    exec "$@"
fi
