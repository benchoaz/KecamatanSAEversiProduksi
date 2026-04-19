# ============================================================
# Multi-Stage Optimized Laravel Dockerfile
# ============================================================

# --- Stage 1: Base Runtime ---
FROM php:8.1-fpm-alpine AS base

# Install runtime system dependencies
RUN apk add --no-cache \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype \
    icu-libs \
    oniguruma \
    libpq \
    imagemagick \
    shadow \
    mysql-client

# Install PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath opcache zip intl gd \
    && pecl install redis-6.0.2 \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Configure PHP/OPcache
COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www

# --- Stage 2: Composer dependencies ---
FROM base AS composer_builder
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# --- Stage 3: Asset compilation ---
FROM node:18-alpine AS node_builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# --- Stage 4: Production Image ---
FROM base AS production

# Copy composer for package discovery
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy vendor from composer_builder
COPY --from=composer_builder /var/www/vendor ./vendor

# Copy built assets from node_builder
COPY --from=node_builder /app/public/build ./public/build

# Copy application code
COPY . .

# Copy custom PHP configurations
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Setup directories, clear stale cache, regenerate autoloader & set permissions
RUN mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/app/public \
    && mkdir -p bootstrap/cache \
    && rm -f bootstrap/cache/*.php \
    && composer dump-autoload --optimize --no-dev --no-interaction --no-scripts \
    && rm /usr/bin/composer \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port 9000
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD php-fpm -t || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
