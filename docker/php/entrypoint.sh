#!/bin/sh

cd /var/www/html

echo "[ENTRYPOINT] Initializing Laravel app..."

# 1) Ensure storage structure exists
mkdir -p storage/framework/views storage/framework/cache storage/logs
touch storage/logs/laravel.log

# Set correct permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 2) Remove Vite hot-reload indicator (prevents :5173 dev server issues)
rm -f public/hot

# 3) Clear stale caches on first container start
php artisan view:clear
php artisan cache:clear
php artisan config:clear

# 4) Run migrations ONCE PER MACHINE
# We use a marker file to ensure migrations don't run again
if [ ! -f /var/www/html/storage/.migrated ]; then
echo "[ENTRYPOINT] Running migrations..."
php artisan migrate --force || true
touch /var/www/html/storage/.migrated
else
echo "[ENTRYPOINT] Migrations already applied. Skipping."
fi

echo "[ENTRYPOINT] Starting PHP-FPM..."
exec php-fpm
