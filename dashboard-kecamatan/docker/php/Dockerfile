# Multi-stage build untuk PHP Laravel - Builder stage
FROM php:8.1-fpm-alpine AS builder

# Install dependencies yang diperlukan composer
RUN apk add --no-cache \
    git \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    icu-dev \
    oniguruma-dev

# Install PHP extensions untuk builder
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Copy composer dari image composer:latest
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy SEMUA files terlebih dahulu (untuk autoload scripts)
COPY . .

# Install dependencies (no-dev untuk production)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Production stage - gunakan alpine (JAUH lebih ringan)
FROM php:8.1-fpm-alpine

# Install system dependencies - HANYA yang diperlukan di runtime
# su-exec needed for user switching in entrypoint
RUN apk add --no-cache \
    libpng \
    libzip \
    libxml2 \
    icu-libs \
    oniguruma \
    su-exec

# Install PHP extensions ONLY (tanpa dev packages)
RUN apk add --no-cache --virtual .php-build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    icu-dev \
    oniguruma-dev && \
    docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl && \
    docker-php-ext-install opcache && \
    apk del .php-build-deps

# Copy opcache config
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy entrypoint script
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set working directory
WORKDIR /var/www

# Copy vendor dari builder stage (jauh lebih cepat)
COPY --from=builder --chown=www-data:www-data /app/vendor /var/www/vendor

# Copy aplikasi source - tanpa vendor (sudah di-copy dari builder)
COPY --chown=www-data:www-data --exclude=vendor . /var/www

# Clean up unnecessary files
RUN rm -rf /var/www/.git /var/www/tests /var/www/docs

# Create required storage directories with proper permissions
RUN mkdir -p /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/storage/framework/cache \
    /var/www/storage/logs \
    /var/www/bootstrap/cache && \
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap && \
    chmod -R 775 /var/www/storage /var/www/bootstrap

# DON'T set USER here - let entrypoint handle it after permission fix
# Container starts as root, fixes permissions, then switches to www-data

# Expose port
EXPOSE 9000

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["php-fpm"]
