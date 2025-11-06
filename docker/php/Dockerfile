# ============================================================
# Stage 1 — Build & Optimize Laravel (Composer + Artisan)
# ============================================================
FROM php:8.3-cli-alpine AS composer_build

# Install system dependencies for composer stage
RUN apk add --no-cache \
    icu-dev libzip-dev oniguruma-dev zip unzip git bash curl

# Install PHP extensions required for build
RUN docker-php-ext-install pdo_mysql intl zip bcmath

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy full application source first (so helpers.php exists)
COPY . .

# Install dependencies
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Laravel optimizations
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache || true


# ============================================================
# Stage 2 — Production Runtime Image (Small & Clean)
# ============================================================
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    icu-dev libzip-dev oniguruma-dev zip unzip git bash curl supervisor \
    && docker-php-ext-install pdo_mysql intl zip bcmath opcache

WORKDIR /var/www/html

# Copy optimized app from builder
COPY --from=composer_build /app ./

# Fix permissions for runtime
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 755 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
