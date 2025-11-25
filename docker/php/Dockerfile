# ============================================================
# Stage 1 — Dependencies (Composer Install with Caching)
# ============================================================
FROM php:8.3-cli-alpine AS vendor

# Install system packages needed for composer install
RUN apk add --no-cache \
    icu-dev libzip-dev oniguruma-dev zip unzip git bash curl

# Install PHP extensions required for dependencies
RUN docker-php-ext-install pdo_mysql intl zip bcmath

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy only composer manifest first (cache layer)
COPY composer.json composer.lock ./

# Copy helpers early so autoload won't break
COPY app/Helpers/helpers.php app/Helpers/helpers.php

# ✅ Install PHP dependencies WITHOUT running artisan scripts
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts


# ============================================================
# Stage 2 — Build Laravel Cache (Config, Routes, Views)
# ============================================================
FROM php:8.3-cli-alpine AS laravel_build

RUN apk add --no-cache \
    icu-dev libzip-dev oniguruma-dev zip unzip git bash curl \
    && docker-php-ext-install pdo_mysql intl zip bcmath

WORKDIR /app

# Copy full application source (this layer rebuilds fast when code changes)
COPY . .
RUN rm -f .env .env.docker || true
# Copy vendor from previous stage
COPY --from=vendor /app/vendor ./vendor

# Laravel optimizations
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache || true


# ============================================================
# Stage 3 — Production Runtime
# ============================================================
FROM php:8.3-fpm-alpine

RUN apk add --no-cache icu-dev libzip-dev oniguruma-dev netcat-openbsd \
    && docker-php-ext-install pdo_mysql intl zip bcmath opcache

WORKDIR /var/www/html

COPY --from=laravel_build /app ./

RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 755 storage bootstrap/cache

EXPOSE 9000

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
