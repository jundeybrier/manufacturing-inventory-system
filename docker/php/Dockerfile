FROM php:8.3-fpm-alpine

# Install PHP extensions and tools
RUN apk add --no-cache \
    icu-dev libzip-dev oniguruma-dev zip unzip git bash curl supervisor \
    && docker-php-ext-install pdo_mysql intl zip bcmath opcache

# Install Composer globally
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy the Laravel app source
COPY . .

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Optimize Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache || true

EXPOSE 9000
CMD ["php-fpm"]
